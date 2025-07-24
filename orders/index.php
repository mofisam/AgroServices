<?php
session_start();
include '../config/db.php';

// 🔒 Ensure buyer is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../login.php");
    exit();
}
include_once '../includes/tracking.php';
// SEO Meta Variables for Orders Page
$page_title = "My Orders | F and V Agro Services Buyer Dashboard";
$page_description = "View and manage your agricultural product purchases on  Fand V Agro Services. Track order status, payment history, and delivery updates.";
$page_keywords = "agricultural orders Nigeria, farm purchase history, agro order tracking, buyer dashboard, F and V Agro Services purchases";
$og_image = "https://www.fandvagroservices.com.ng/assets/images/orders-social-preview.jpg";
$current_url = "https://www.fandvagroservices.com.ng/buyer/orders";

include '../includes/header.php';
$buyer_id = $_SESSION['user_id'];

// ✅ Fetch buyer's orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE buyer_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();
// Additional security headers (add to your config/db.php or header.php)
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
?>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebPage",
  "name": "Order History",
  "description": "Agricultural product purchase records",
  "isAccessibleForFree": false,
  "hasPart": {
    "@type": "WebPageElement",
    "name": "Order Tracking",
    "description": "Status updates for farm product purchases"
  }
}
</script>

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
              <td><?= number_format($order['total_amount']/100) ?></td>
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