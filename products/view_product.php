<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

$product_id = $_GET['id'] ?? 0;

// 🔍 Fetch product
$stmt = $conn->prepare("
  SELECT p.*, ba.business_name 
  FROM products p 
  JOIN users u ON u.id = p.seller_id 
  JOIN business_accounts ba ON ba.user_id = u.id 
  WHERE p.id = ? AND p.status = 'active'
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
  echo "<div class='container mt-5'><div class='alert alert-danger'>Product not found.</div></div>";
  include '../includes/footer.php';
  exit;
}

// ✅ Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'])) {
  $qty = max(1, (int)$_POST['quantity']);
  $_SESSION['cart'][$product_id] = ['quantity' => $qty];
  $success = true;
}

// 🔍 Fetch reviews
$reviews = $conn->prepare("SELECT r.*, u.first_name, u.last_name FROM product_reviews r JOIN users u ON u.id = r.user_id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$reviews->bind_param("i", $product_id);
$reviews->execute();
$review_result = $reviews->get_result();

// 🔢 Avg Rating
$avg_rating = $conn->query("SELECT ROUND(AVG(rating),1) AS avg FROM product_reviews WHERE product_id = $product_id")->fetch_assoc()['avg'] ?? null;
?>

<div class="container py-5">
  <div class="row">
    <div class="col-md-6">
      <?php if (!empty($product['image'])): ?>
        <img src="<?= htmlspecialchars($product['image']) ?>" class="img-fluid rounded shadow">
      <?php else: ?>
        <div class="bg-light p-5 text-center border rounded">No image available</div>
      <?php endif; ?>
    </div>

    <div class="col-md-6">
      <h2><?= htmlspecialchars($product['name']) ?></h2>
      <p><strong>Business:</strong> <?= htmlspecialchars($product['business_name']) ?></p>
      <h4 class="text-success">₦<?= number_format($product['price']) ?></h4>
      <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>

      <?php if (isset($success)): ?>
        <div class="alert alert-success">✅ Added to cart. <a href="../cart/index.php" class="btn btn-sm btn-success ms-2">🛒 View Cart</a></div>
      <?php endif; ?>

      <form method="POST" class="mt-4">
        <div class="mb-3">
          <label for="quantity" class="form-label">Quantity</label>
          <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1" required>
        </div>
        <button type="submit" class="btn btn-primary">🛒 Add to Cart</button>
        <a href="ask_seller.php?id=<?= $product_id ?>" class="btn btn-outline-secondary ms-2">💬 Message Seller</a>
      </form>

      <?php if ($avg_rating): ?>
        <div class="mt-3">
          <h6>⭐ Average Rating: <?= $avg_rating ?>/5</h6>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ⭐ Existing Reviews Only -->
  <div class="card my-5 shadow-sm">
    <div class="card-body">
      <h4 class="mb-3">What Others Are Saying</h4>

      <?php if ($avg_rating): ?>
        <div class="mb-3">
          <h6>Average Rating: ⭐ <?= $avg_rating ?>/5</h6>
        </div>
      <?php endif; ?>

      <?php if ($review_result->num_rows > 0): ?>
        <?php while ($r = $review_result->fetch_assoc()): ?>
          <div class="mb-4 border-bottom pb-3">
            <strong><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></strong>
            <span class="text-warning">⭐ <?= $r['rating'] ?>/5</span><br>
            <small class="text-muted"><?= date('M d, Y h:i A', strtotime($r['created_at'])) ?></small>
            <p class="mt-2"><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-muted">This product has not been reviewed yet.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
