<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../login.php");
    exit();
}

$ref = $_GET['ref'] ?? '';
$buyer_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM orders WHERE payment_reference = ? AND buyer_id = ?");
$stmt->bind_param("si", $ref, $buyer_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo '<div class="alert alert-danger container mt-5">Invalid order or unauthorized access.</div>';
    include '../includes/footer.php';
    exit();
}

$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order['id']);
$stmt->execute();
$items = $stmt->get_result();
?>

<div class="container py-5" id="invoiceArea">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>🧾 Order Details</h2>
    <a href="invoice_download.php?ref=<?= $order['payment_reference'] ?>" class="btn btn-success">📥 Download PDF Invoice</a>
  </div>

  <!-- Order Info -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body">
      <h5>Order Reference: <span class="text-success"><?= htmlspecialchars($order['payment_reference']) ?></span></h5>
      <p>Status:
        <?php if ($order['payment_status'] === 'paid'): ?>
          <span class="badge bg-success">Paid</span>
        <?php else: ?>
          <span class="badge bg-warning text-dark"><?= ucfirst($order['payment_status']) ?></span>
        <?php endif; ?>
      </p>
      <p><strong>Total:</strong> ₦<?= number_format($order['total_amount']) ?></p>
      <p><strong>Date:</strong> <?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></p>
      <p><strong>Delivery:</strong>
        <?php if ($order['delivery_status'] === 'delivered'): ?>
          <span class="badge bg-primary">Delivered</span>
        <?php elseif ($order['delivery_status'] === 'in-transit'): ?>
          <span class="badge bg-info text-dark">In Transit</span>
        <?php else: ?>
          <span class="badge bg-secondary">Pending</span>
        <?php endif; ?>
      </p>
    </div>
  </div>

  <!-- Shipping Info -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body">
      <h5>Shipping Information</h5>
      <p><strong>Name:</strong> <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
      <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
      <p><strong>Address:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>
      <p><strong>State:</strong> <?= htmlspecialchars($order['state']) ?></p>
    </div>
  </div>

  <!-- Product List -->
  <div class="card shadow-sm">
    <div class="card-body">
      <h5>Items Ordered</h5>
      <?php if ($items->num_rows > 0): ?>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Product</th>
              <th>Business</th>
              <th>Price (₦)</th>
              <th>Qty</th>
              <th>Subtotal (₦)</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              $total = 0;
              while ($item = $items->fetch_assoc()):
                $product_info = getProductMeta($conn, $item['product_id']);
                $discount_percent = (int)($product_info['discount_percent'] ?? 0);
                $original_price = (float)($product_info['price'] ?? $item['price']);
                
                // Calculate the discounted price
                if ($discount_percent > 0) {
                    $price = round($original_price * (1 - $discount_percent / 100), 2);
                } else {
                    $price = $original_price;
                }

                $savings = $discount_percent > 0 ? $original_price - $price : 0;
                $subtotal = $price * $item['quantity']; // Corrected subtotal calculation
                $total += $subtotal;
            ?>
              <tr>
                <td>
                  <?= htmlspecialchars($product_info['name'] ?? 'Unknown') ?><br>
                  <?php if ($discount_percent > 0): ?>
                    <small><del>₦<?= number_format($original_price) ?></del> → ₦<?= number_format($price) ?> 
                      <span class="badge bg-danger ms-1">-<?= $discount_percent ?>%</span>
                    </small>
                  <?php else: ?>
                    <small>₦<?= number_format($price) ?></small>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($item['business_name']) ?></td>
                <td>₦<?= number_format($price) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>₦<?= number_format($subtotal) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        <div class="text-end fs-5 fw-bold">Total Paid: ₦<?= number_format($total) ?></div>
      <?php else: ?>
        <div class="alert alert-info">No items found for this order.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- 🖨 Invoice Print Script -->
<script>
function printInvoice() {
  const area = document.getElementById("invoiceArea").innerHTML;
  const win = window.open('', '', 'height=800,width=800');
  win.document.write('<html><head><title>Invoice</title>');
  win.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
  win.document.write('</head><body>');
  win.document.write(area);
  win.document.write('</body></html>');
  win.document.close();
  win.print();
}
</script>

<?php
// Enhanced product meta fetch
function getProductMeta($conn, $product_id) {
    $stmt = $conn->prepare("SELECT name, price, discount_percent FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $product = $res->fetch_assoc();
    $stmt->close();
    return $product ?? [];
}

include '../includes/footer.php';
?>
