<?php
include '../config/db.php';

$business = $_GET['business'] ?? '';
if (!$business) die("Business not specified.");

$stmt = $conn->prepare("
  SELECT p.*, ba.business_name,
    (SELECT ROUND(AVG(r.rating),1) FROM product_reviews r WHERE r.product_id = p.id) AS avg_rating
  FROM products p
  JOIN users u ON u.id = p.seller_id
  JOIN business_accounts ba ON ba.user_id = u.id
  WHERE ba.business_name = ? AND p.status = 'active'
");
$stmt->bind_param("s", $business);
$stmt->execute();
$products = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
  <title><?= htmlspecialchars($business) ?> - Storefront</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
  <h2>🏪 <?= htmlspecialchars($business) ?> Storefront</h2>

  <div class="row">
    <?php if ($products->num_rows > 0): ?>
      <?php while ($p = $products->fetch_assoc()): ?>
        <div class="col-md-3 mb-3">
          <?php include 'components/product_card.php'; ?>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="alert alert-warning">No products found for this business.</div>
    <?php endif; ?>
  </div>

  <a href="index.php" class="btn btn-secondary">← Back to Marketplace</a>
</body>
</html>