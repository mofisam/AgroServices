<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch wishlist
$stmt = $conn->prepare("
  SELECT w.product_id, p.name, p.price, p.image
  FROM wishlists w
  JOIN products p ON w.product_id = p.id
  WHERE w.user_id = ?
  ORDER BY w.added_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wishlist = $stmt->get_result();
?>

<div class="container py-5">
  <h2 class="mb-4">❤️ My Wishlist</h2>

  <?php if ($wishlist->num_rows > 0): ?>
    <div class="row">
      <?php while ($item = $wishlist->fetch_assoc()): ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100 shadow-sm">
            <?php if (!empty($item['image'])): ?>
              <img src="<?= htmlspecialchars($item['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?>">
            <?php else: ?>
              <div class="bg-light p-5 text-center">No Image</div>
            <?php endif; ?>

            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
              <p class="card-text text-success">₦<?= number_format($item['price']) ?></p>
              <a href="../products/view_product?id=<?= $item['product_id'] ?>" class="btn btn-outline-primary btn-sm">🔍 View</a>
              <a href="remove?id=<?= $item['product_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove from wishlist?')">🗑 Remove</a>
              <form action="../cart/add_to_cart.php" method="POST" class="d-inline-block ms-1">
                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                <input type="hidden" name="quantity" value="1">
                <button class="btn btn-sm btn-success">🛒 Add to Cart</button>
              </form>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="alert alert-info">Your wishlist is empty. <a href="../products/index">Browse products</a>.</div>
  <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>