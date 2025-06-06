<?php
ob_start();
session_start();
include '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: /login');
  exit();
}
// Redirect if cart is empty
if (!isset($_SESSION['cart']) || count($_SESSION['cart']) === 0) {
    header('Location: /products/index');
    exit();
}

// Optional: Get user info
$user_id = $_SESSION['user_id'] ?? null;
$billing = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'state' => ''
];

if ($user_id) {
    $stmt = $conn->prepare("SELECT first_name, last_name, email, phone, address, state FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($billing['first_name'], $billing['last_name'], $billing['email'], $billing['phone'], $billing['address'], $billing['state']);
    $stmt->fetch();
    $stmt->close();
}

// Get cart data
$cart = $_SESSION['cart'];
$cart_items = [];
$grand_total = 0;

foreach ($cart as $product_id => $item) {
    $quantity = is_array($item) ? $item['quantity'] : (int)$item;

    $query = $conn->prepare("SELECT id, name, price, discount_percent FROM products WHERE id = ? AND status = 'active'");
    $query->bind_param("i", $product_id);
    $query->execute();
    $result = $query->get_result();

    if ($product = $result->fetch_assoc()) {
        $discount = (int)($product['discount_percent'] ?? 0);
        $price = (float)$product['price'];
        $final_price = $discount > 0 ? round($price * (1 - $discount / 100)) : $price;

        $product['quantity'] = $quantity;
        $product['unit_price'] = $final_price;
        $product['original_price'] = $price;
        $product['subtotal'] = $final_price * $quantity;
        $product['discount_percent'] = $discount;

        $cart_items[] = $product;
        $grand_total += $product['subtotal'];
    }
}
?>

<div class="container py-5">
  <h2 class="mb-4">🛒 Step 1: Checkout - Billing Details</h2>

  <div class="row">
    <!-- 🧾 Billing Details -->
    <div class="col-md-6">
      <div class="card shadow-sm p-4">
        <h4 class="mb-3 text-success">Billing Information</h4>
        <form method="POST" action="step2">
          <div class="mb-3">
            <label>First Name</label>
            <input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($billing['first_name']) ?>">
          </div>
          <div class="mb-3">
            <label>Last Name</label>
            <input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($billing['last_name']) ?>">
          </div>
          <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($billing['email']) ?>">
          </div>
          <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" required value="<?= htmlspecialchars($billing['phone']) ?>">
          </div>
          <div class="mb-3">
            <label>State</label>
            <input type="text" name="state" class="form-control" required value="<?= htmlspecialchars($billing['state']) ?>">
          </div>
          <div class="mb-3">
            <label>Address</label>
            <textarea name="address" class="form-control" required><?= htmlspecialchars($billing['address']) ?></textarea>
          </div>

          <input type="hidden" name="grand_total" value="<?= $grand_total ?>">
          <button type="submit" class="btn btn-success w-100">Proceed to Payment</button>
        </form>
      </div>
    </div>

    <!-- 🛍️ Cart Summary -->
    <div class="col-md-6">
      <div class="card shadow-sm p-4">
        <h4 class="mb-3 text-success">Order Summary</h4>
        <?php foreach ($cart_items as $item): ?>
          <div class="d-flex justify-content-between border-bottom mb-2">
            <div>
              <strong><?= htmlspecialchars($item['name']) ?></strong><br>
              <?php if ($item['discount_percent'] > 0): ?>
                <small>
                  <del>₦<?= number_format($item['original_price']) ?></del>
                  ₦<?= number_format($item['unit_price']) ?> x <?= $item['quantity'] ?>
                  <span class="badge bg-danger ms-1">-<?= $item['discount_percent'] ?>%</span>
                </small>
              <?php else: ?>
                <small>₦<?= number_format($item['unit_price']) ?> x <?= $item['quantity'] ?></small>
              <?php endif; ?>
            </div>
            <div><strong>₦<?= number_format($item['subtotal']) ?></strong></div>
          </div>
        <?php endforeach; ?>
        <hr>
        <div class="d-flex justify-content-between">
          <strong>Total:</strong>
          <strong class="text-success">₦<?= number_format($grand_total) ?></strong>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
<?php ob_end_flush(); ?>
