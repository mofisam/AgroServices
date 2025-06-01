<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../login.php");
    exit();
}
include '../includes/header.php';

$ref = $_GET['ref'] ?? '';
$buyer_id = $_SESSION['user_id'];

// 🔎 **Fetch Order Information**
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

// 🔎 **Fetch Order Items**
$stmt = $conn->prepare("
    SELECT oi.*, p.name AS product_name, p.price AS product_price, p.discount_percent, ba.business_name 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN business_accounts ba ON ba.user_id = p.seller_id
    WHERE oi.order_id = ?
");
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
      <p><strong>Total:</strong> ₦<?= number_format($order['total_amount']/100) ?></p>
      <p><strong>Date:</strong> <?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></p>
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
              <th>Discount (%)</th>
              <th>Subtotal (₦)</th>
              <th>Delivery Status</th>
            </tr>
          </thead>
          <tbody>
            <?php 
              $total = 0;
              while ($item = $items->fetch_assoc()):
                $price = $item['product_price'];
                $discount = $item['discount_percent'];
                
                // Calculate final price and subtotal
                $final_price = $discount > 0 ? round($price * (1 - $discount / 100), 2) : $price;
                $subtotal = $final_price * $item['quantity'];
                $total += $subtotal;
            ?>
              <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= htmlspecialchars($item['business_name']) ?></td>
                <td>₦<?= number_format($final_price) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= $discount ?>%</td>
                <td>₦<?= number_format($subtotal) ?></td>
                <td>
                  <?php if ($item['delivery_status'] === 'delivered'): ?>
                    <span class="badge bg-primary">Delivered</span>
                  <?php elseif ($item['delivery_status'] === 'in-transit'): ?>
                    <span class="badge bg-info text-dark">In Transit</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Pending</span>
                  <?php endif; ?>
                </td>
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

<?php include '../includes/footer.php'; ?>
