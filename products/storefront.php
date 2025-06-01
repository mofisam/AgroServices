<?php
include '../config/db.php';

$business = $_GET['business'] ?? '';
if (!$business) die("Business not specified.");

// Get business info
$stmt = $conn->prepare("
  SELECT ba.*, u.profile_picture, 
    (SELECT COUNT(*) FROM products WHERE seller_id = u.id AND status = 'active') AS product_count,
    (SELECT ROUND(AVG(r.rating),1) FROM product_reviews r 
     JOIN products p ON r.product_id = p.id 
     WHERE p.seller_id = u.id) AS avg_rating
  FROM business_accounts ba
  JOIN users u ON u.id = ba.user_id
  WHERE ba.business_name = ?
");
$stmt->bind_param("s", $business);
$stmt->execute();
$business_info = $stmt->get_result()->fetch_assoc();

// Get products with additional sorting options
$sort = $_GET['sort'] ?? 'newest';
$sort_sql = match($sort) {
    'price_asc' => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'rating' => 'avg_rating DESC',
    default => 'p.created_at DESC'
};

$stmt = $conn->prepare("
  SELECT p.*, 
    (SELECT ROUND(AVG(r.rating),1) FROM product_reviews r WHERE r.product_id = p.id) AS avg_rating,
    (SELECT COUNT(*) FROM product_reviews r WHERE r.product_id = p.id) AS review_count
  FROM products p
  JOIN users u ON u.id = p.seller_id
  JOIN business_accounts ba ON ba.user_id = u.id
  WHERE ba.business_name = ? AND p.status = 'active'
  ORDER BY $sort_sql
");
$stmt->bind_param("s", $business);
$stmt->execute();
$products = $stmt->get_result();
?>

<?php include '../includes/header.php'; ?>

<body class="bg-light">
<div class="container py-4">
  <!-- Business Header -->
  <div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
      <div class="row align-items-center">
        <div class="col-md-2 text-center">
          <img src="<?= $business_info['profile_picture'] ? '../uploads/profile_pics/'.$business_info['profile_picture'] : '../assets/images/default-store.png' ?>" 
               class="rounded-circle img-thumbnail" width="120" height="120" style="object-fit: cover;">
        </div>
        <div class="col-md-6">
          <h1 class="mb-2"><?= htmlspecialchars($business_info['business_name']) ?></h1>
          <div class="d-flex align-items-center mb-2">
            <?php if ($business_info['avg_rating']): ?>
              <div class="star-rating me-2" style="--rating: <?= $business_info['avg_rating'] ?>;"></div>
              <span class="text-muted"><?= $business_info['avg_rating'] ?> (<?= $business_info['product_count'] ?> products)</span>
            <?php else: ?>
              <span class="text-muted">No ratings yet</span>
            <?php endif; ?>
          </div>
          <p class="text-muted mb-2">
            <i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($business_info['business_address']) ?>
          </p>
          
        </div>
        <div class="col-md-4">
          <div class="card bg-light">
            <div class="card-body text-center">
              <h5 class="card-title">Business Info</h5>
              <div class="d-flex justify-content-between mb-2">
                <span>Products:</span>
                <strong><?= $business_info['product_count'] ?></strong>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span>Rating:</span>
                <strong><?= $business_info['avg_rating'] ? $business_info['avg_rating'].'/5' : 'N/A' ?></strong>
              </div>
              <div class="d-flex justify-content-between">
                <span>Member Since:</span>
                <strong><?= date('M Y', strtotime($business_info['created_at'])) ?></strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Products Section -->
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
      <h3 class="mb-0">Products</h3>
      <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown">
          <i class="bi bi-sort-down me-1"></i> 
          <?= match($sort) {
              'price_asc' => 'Price: Low to High',
              'price_desc' => 'Price: High to Low',
              'rating' => 'Top Rated',
              default => 'Newest First'
          } ?>
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="?business=<?= urlencode($business) ?>&sort=newest">Newest First</a></li>
          <li><a class="dropdown-item" href="?business=<?= urlencode($business) ?>&sort=price_asc">Price: Low to High</a></li>
          <li><a class="dropdown-item" href="?business=<?= urlencode($business) ?>&sort=price_desc">Price: High to Low</a></li>
          <li><a class="dropdown-item" href="?business=<?= urlencode($business) ?>&sort=rating">Top Rated</a></li>
        </ul>
      </div>
    </div>
    <div class="card-body">
      <?php if ($products->num_rows > 0): ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
          <?php while ($p = $products->fetch_assoc()): ?>
            <div class="col">
              <div class="card h-100 border-0 shadow-sm">
                <?php if ($p['discount_percent'] > 0): ?>
                  <span class="badge bg-danger position-absolute top-0 end-0 m-2">-<?= $p['discount_percent'] ?>%</span>
                <?php endif; ?>
                <img src="<?= $p['image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($p['name']) ?>" style="height: 200px; object-fit: cover;">
                <div class="card-body">
                  <h5 class="card-title"><?= htmlspecialchars($p['name']) ?></h5>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                      <?php if ($p['avg_rating']): ?>
                        <div class="star-rating small me-2" style="--rating: <?= $p['avg_rating'] ?>;"></div>
                        <small class="text-muted">(<?= $p['review_count'] ?>)</small>
                      <?php else: ?>
                        <small class="text-muted">No reviews</small>
                      <?php endif; ?>
                    </div>
                    <div>
                      <?php if ($p['stock'] > 0): ?>
                        <small class="text-success">In Stock (<?= $p['stock'] ?>)</small>
                      <?php else: ?>
                        <small class="text-danger">Out of Stock</small>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="d-flex justify-content-between align-items-end">
                    <div>
                      <?php if ($p['discount_percent'] > 0): ?>
                        <span class="text-danger fs-5 fw-bold">₦<?= number_format($p['price'] * (1 - $p['discount_percent']/100), 2) ?></span>
                        <small class="text-muted text-decoration-line-through ms-1">₦<?= number_format($p['price'], 2) ?></small>
                      <?php else: ?>
                        <span class="fs-5 fw-bold">₦<?= number_format($p['price'], 2) ?></span>
                      <?php endif; ?>
                    </div>
                    <a href="view_product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary">
                      <i class="bi bi-eye me-1"></i> View
                    </a>
                  </div>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="text-center py-5">
          <i class="bi bi-box-seam display-5 text-muted mb-3"></i>
          <h4>No Products Available</h4>
          <p class="text-muted">This business hasn't listed any products yet.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <a href="../index.php" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i> Back to Marketplace
  </a>
</div>

<style>
.star-rating {
  --percent: calc(var(--rating) / 5 * 100%);
  display: inline-block;
  font-size: 1.2rem;
  line-height: 1;
}
.star-rating::before {
  content: '★★★★★';
  letter-spacing: 2px;
  background: linear-gradient(90deg, #ffc107 var(--percent), #ddd var(--percent));
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
}
.star-rating.small {
  font-size: 1rem;
}
</style>

<?php include '../includes/footer.php'; ?>
</body>
</html>