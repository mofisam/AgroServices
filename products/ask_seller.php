<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    die("Missing product ID.");
}

// 🔍 Get product and seller info
$stmt = $conn->prepare("
  SELECT p.id, p.name, u.id AS seller_id, ba.business_name
  FROM products p
  JOIN users u ON u.id = p.seller_id
  JOIN business_accounts ba ON ba.user_id = u.id
  WHERE p.id = ?
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();

if (!$p) die("Invalid product.");

$success = null;

// 📝 Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');

    if ($message) {
        $stmt = $conn->prepare("INSERT INTO product_inquiries 
            (product_id, seller_id, user_id, message, sender_role) 
            VALUES (?, ?, ?, ?, 'buyer')");
        $stmt->bind_param("iiis", $p['id'], $p['seller_id'], $user_id, $message);
        if ($stmt->execute()) {
            $success = "✅ Your message has been sent to the business.";
        } else {
            $success = "❌ Failed to send message.";
        }
    } else {
        $success = "❗ Please enter your message.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Ask About <?= htmlspecialchars($p['name']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">

  <h2>💬 Ask About “<?= htmlspecialchars($p['name']) ?>”</h2>
  <p><strong>Business:</strong> <?= htmlspecialchars($p['business_name']) ?></p>

  <?php if ($success): ?>
    <div class="alert alert-info"><?= $success ?></div>
  <?php endif; ?>

  <form method="POST" class="mt-4">
    <div class="mb-3">
      <label>Your Message</label>
      <textarea name="message" class="form-control" rows="5" required></textarea>
    </div>
    <button class="btn btn-primary">📨 Send Message</button>
  </form>

  <a href="view_product.php?id=<?= $product_id ?>" class="btn btn-link mt-3">← Back to Product</a>
</body>
</html>
