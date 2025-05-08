<?php
ob_start(); // Start output buffering
// 🔒 Secure the page
session_start();
include '../config/db.php';
include '../includes/header.php';

// 🔒 Ensure buyer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../login.php");
    exit();
}

$buyer_id = $_SESSION['user_id'];

// ✅ Fetch buyer's orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE buyer_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container py-5">
  <h2 class="mb-4">📦 My Orders</h2>

  <?php if ($result->num_rows > 0): ?>
    <div class="table-responsive">
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-success">
          <tr>
            <th>#</th>
            <th>Reference</th>
            <th>Total (₦)</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1; while ($order = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($order['payment_reference']) ?></td>
              <td><?= number_format($order['total_amount']) ?></td>
              <td>
                <?php if ($order['payment_status'] === 'paid'): ?>
                  <span class="badge bg-success">Paid</span>
                <?php else: ?>
                  <span class="badge bg-warning text-dark"><?= ucfirst($order['payment_status']) ?></span>
                <?php endif; ?>
              </td>
              <td><?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></td>
              <!-- Optional: View details button -->
              <td><a href="order_details.php?ref=<?= $order['payment_reference'] ?>" class="btn btn-sm btn-primary">View</a></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="alert alert-info">You have not placed any orders yet.</div>
  <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
<? ob_flush(); // Flush the output buffer ?>