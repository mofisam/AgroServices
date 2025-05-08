<?php
session_start();
include '../config/db.php';

$user_id = $_SESSION['user_id'] ?? null;
$user_location = $_SESSION['user_location'] ?? 'Lagos';

// 🔥 Trending
$trending = $conn->query("
  SELECT p.*, ba.business_name,
    (SELECT ROUND(AVG(r.rating),1) FROM product_reviews r WHERE r.product_id = p.id) AS avg_rating
  FROM products p
  JOIN users u ON u.id = p.seller_id
  JOIN business_accounts ba ON ba.user_id = u.id
  WHERE p.status='active'
  ORDER BY p.impressions DESC
  LIMIT 6
");

// 🧠 AI-Based Recommended Products
$ai_sql = "
  SELECT DISTINCT p.*, ba.business_name,
    (SELECT ROUND(AVG(r.rating),1) FROM product_reviews r WHERE r.product_id = p.id) AS avg_rating
  FROM products p
  JOIN users u ON u.id = p.seller_id
  JOIN business_accounts ba ON ba.user_id = u.id
  WHERE p.status = 'active'
";

if ($user_id) {
  $tags_result = $conn->prepare("
    SELECT GROUP_CONCAT(DISTINCT p.tags SEPARATOR ',') AS all_tags
    FROM product_clicks pc
    JOIN products p ON pc.product_id = p.id
    WHERE pc.user_id = ?
  ");
  $tags_result->bind_param("i", $user_id);
  $tags_result->execute();
  $tag_row = $tags_result->get_result()->fetch_assoc();
  $tags_combined = strtolower($tag_row['all_tags'] ?? '');

  $keywords = array_filter(array_unique(array_map('trim', explode(',', $tags_combined))));
  $keyword_like_clauses = [];
  $params = [];
  $types = '';

  foreach ($keywords as $kw) {
    $keyword_like_clauses[] = "p.tags LIKE ?";
    $params[] = "%$kw%";
    $types .= 's';
  }

  if (!empty($keyword_like_clauses)) {
    $ai_sql .= " AND (" . implode(' OR ', $keyword_like_clauses) . ")";
    $stmt = $conn->prepare($ai_sql . " ORDER BY RAND() LIMIT 6");
    $stmt->bind_param($types, ...$params);
  } else {
    $ai_sql .= " AND p.location LIKE ?";
    $stmt = $conn->prepare($ai_sql . " ORDER BY RAND() LIMIT 6");
    $loc = "%$user_location%";
    $stmt->bind_param("s", $loc);
  }
} else {
  $ai_sql .= " AND p.tags LIKE ?";
  $stmt = $conn->prepare($ai_sql . " ORDER BY RAND() LIMIT 6");
  $default_kw = '%seed%';
  $stmt->bind_param("s", $default_kw);
}

$stmt->execute();
$suggestions = $stmt->get_result();

// 🔖 Categories
$categories = $conn->query("SELECT id, name FROM product_categories WHERE is_deleted = 0 ORDER BY name");

$page_title = "Product Discovery";
$page_description = "Browse agriculture products, equipment, and innovations from verified sellers.";
$page_keywords = "Agro, ecommerce, farm tools, produce, Nigeria";

include '../includes/header.php';
?>

<div class="container py-4">
  <h2 class="mb-4">🛒 Discover Products</h2>

  <!-- 🔍 Filter -->
  <form id="searchForm" class="row g-3 mb-4">
    <div class="col-md-4">
      <input type="text" name="search" placeholder="Search products..." class="form-control">
    </div>
    <div class="col-md-2">
      <select name="category" class="form-select">
        <option value="">All Categories</option>
        <?php while ($cat = $categories->fetch_assoc()): ?>
          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-2">
      <input type="number" name="min_price" placeholder="Min ₦" class="form-control">
    </div>
    <div class="col-md-2">
      <input type="number" name="max_price" placeholder="Max ₦" class="form-control">
    </div>
    <div class="col-md-2">
      <button class="btn btn-dark w-100">Filter</button>
    </div>
  </form>

  <div class="row">
    <!-- 🛍️ Main Grid -->
    <div class="col-md-9">
      <h4 class="mb-3">🛍️ All Products</h4>
      <div id="productGrid" class="row"></div>
      <nav id="paginationControls" class="mt-4"></nav>
    </div>

    <!-- 🧠 Sidebar -->
    <div class="col-md-3">
      <h5 class="mb-3">🧠 Recommended For You</h5>
      <?php if ($suggestions->num_rows > 0): ?>
        <?php while ($p = $suggestions->fetch_assoc()): ?>
          <div class="mb-3">
            <?php include 'components/product_card_mini.php'; ?>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="text-muted">No personalized suggestions found.</div>
      <?php endif; ?>

      <h5 class="mt-4 mb-3">🔥 Trending Now</h5>
      <?php if ($trending->num_rows > 0): ?>
        <?php while ($t = $trending->fetch_assoc()): ?>
          <div class="mb-3">
            <?php $p = $t; include 'components/product_card_mini.php'; ?>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="text-muted">No trending products.</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Quick View Modal -->
  <div class="modal fade" id="quickViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">🔍 Quick View</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">Loading...</div>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function loadProducts(page = 1) {
  const formData = $('#searchForm').serializeArray().filter(f => f.value.trim() !== '');
  formData.push({ name: 'page', value: page });

  $.get('fetch_products.php', $.param(formData), function (data) {
    $('#productGrid').html(data.grid || '<div class="text-muted">No products found.</div>');
    $('#paginationControls').html(data.pagination || '');
  }, 'json').fail(function (xhr) {
    console.error(xhr.responseText);
    $('#productGrid').html('<div class="alert alert-danger">Failed to load products.</div>');
  });
}

$(function () {
  loadProducts();

  $('#searchForm').on('submit', function (e) {
    e.preventDefault();
    loadProducts();
  });

  $('#searchForm select, #searchForm input').on('change', function () {
    $('#searchForm').trigger('submit');
  });

  $(document).on('click', '.pagination a', function (e) {
    e.preventDefault();
    loadProducts($(this).data('page'));
  });

  $(document).on('click', '.view-btn', function () {
    let id = $(this).data("id");
    $("#quickViewModal .modal-body").html("Loading...");
    $.get("quick_view.php", { id }, function (html) {
      $("#quickViewModal .modal-body").html(html);
    });
    new bootstrap.Modal(document.getElementById("quickViewModal")).show();
  });

  $(document).on('click', '.add-cart', function () {
    const id = $(this).data('id');
    $.post('../cart/add_to_cart.php', { product_id: id, quantity: 1 }, function () {
      alert("Added to cart!");
    });
  });
});
</script>
<script>
function trackProductClick(productId) {
  $.post('../products/track_click.php', { product_id: productId });
}

function addToWishlist(productId) {
  $.post('wishlist_add.php', { product_id: productId }, function (res) {
    alert("Added to wishlist 💖");
  });
}
</script>

<?php include '../includes/footer.php'; ?>
