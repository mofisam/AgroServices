<?php
session_start();
include '../includes/header.php';

// Get order reference from URL
$ref = $_GET['ref'] ?? '';

// Optional: Fetch order from DB (if needed)
// $stmt = $conn->prepare("SELECT * FROM orders WHERE payment_reference = ?");
// $stmt->bind_param("s", $ref);
// $stmt->execute();
// $order = $stmt->get_result()->fetch_assoc();
// $stmt->close();

?>

<div class="container py-5 text-center">
  <div class="card shadow-sm p-5">
    <h1 class="text-success display-4 mb-3">🎉 Thank You!</h1>
    <p class="lead">Your order was successful.</p>

    <?php if ($ref): ?>
      <p class="text-muted">🧾 Reference: <strong><?= htmlspecialchars($ref) ?></strong></p>
    <?php endif; ?>

    <hr class="my-4">

    <p class="text-muted mb-4">
      We've received your payment and your order is now being processed.  
      You'll get an email update soon.
    </p>

    <a href="/dashboard.php" class="btn btn-success btn-lg me-2">Go to Dashboard</a>
    <a href="/products/index.php" class="btn btn-outline-secondary btn-lg">Continue Shopping</a>
  </div>
</div>

<?php include '../includes/footer.php'; ?>
