<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login");
  exit();
}
include '../includes/header.php';
include_once '../includes/tracking.php';

$cart = $_SESSION['cart'] ?? [];

$total = 0;
?>

<div class="container py-5">
  <h2 class="mb-4">🛒 Your Shopping Cart</h2>

  <?php if (empty($cart)): ?>
    <div class="alert alert-info">Your cart is empty. <a href="../products/index">Start shopping</a>.</div>
  <?php else: ?>
    <form method="post" action="update_cart.php">
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead class="table-success">
            <tr>
              <th>Product</th>
              <th>Price (₦)</th>
              <th>Qty</th>
              <th>Subtotal (₦)</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cart as $product_id => $item): ?>
              <?php
              $product_id = (int)$product_id;
              $qty = is_array($item) ? (int)$item['quantity'] : (int)$item;

              $stmt = $conn->prepare("SELECT name, price, discount_percent FROM products WHERE id = ? AND status = 'active'");
              $stmt->bind_param("i", $product_id);
              $stmt->execute();
              $res = $stmt->get_result();
              $product = $res->fetch_assoc();
              $stmt->close();

              if (!$product) continue;

              $price = (float)$product['price'];
              $discount = (int)$product['discount_percent'];
              $discounted_price = $discount > 0 ? round($price * (1 - $discount / 100)) : $price;

              $subtotal = $discounted_price * $qty;
              $total += $subtotal;
              ?>
              <tr>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td>
                  <strong>₦<?= number_format($discounted_price) ?></strong>
                  <?php if ($discount > 0): ?>
                    <br><small class="text-muted text-decoration-line-through">₦<?= number_format($price) ?></small>
                    <span class="badge bg-danger ms-1">-<?= $discount ?>%</span>
                  <?php endif; ?>
                </td>
                <td>
                  <input type="number" name="quantities[<?= $product_id ?>]" value="<?= $qty ?>" min="1" class="form-control form-control-sm" required>
                </td>
                <td>₦<?= number_format($subtotal) ?></td>
                <td>
                  <a href="remove_item.php?id=<?= $product_id ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove this item?')">Remove</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="text-end mb-4">
        <h4>Total: ₦<?= number_format($total) ?></h4>
        <button type="submit" class="btn btn-secondary">Update Cart</button>
        <a href="../checkout/step1" class="btn btn-success">Proceed to Checkout</a>
      </div>
    </form>
  <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
