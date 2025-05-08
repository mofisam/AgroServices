<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') exit;

$user_id = $_SESSION['user_id'];
$seller_id = $_GET['seller_id'];

$stmt = $conn->prepare("
  SELECT i.*, ba.business_name, p.name AS product_name
  FROM product_inquiries i
  JOIN products p ON p.id = i.product_id
  JOIN business_accounts ba ON ba.user_id = i.seller_id
  WHERE i.user_id = ? AND i.seller_id = ?
  ORDER BY i.created_at ASC
");
$stmt->bind_param("ii", $user_id, $seller_id);
$stmt->execute();
$msgs = $stmt->get_result();

// mark seller messages as read
$conn->query("UPDATE product_inquiries SET read_status='read'
              WHERE user_id = $user_id AND seller_id = $seller_id AND sender_role='seller' AND read_status='unread'");

while ($m = $msgs->fetch_assoc()):
?>
  <div class="message <?= $m['sender_role'] === 'seller' ? 'msg-seller' : 'msg-buyer' ?>">
    <strong><?= $m['sender_role'] === 'seller' ? htmlspecialchars($m['business_name']) : 'You' ?>:</strong>
    <div><?= nl2br(htmlspecialchars($m['message'])) ?></div>
    <small class="text-muted d-block"><?= $m['created_at'] ?> | 🛒 <?= htmlspecialchars($m['product_name']) ?></small>
  </div>
<?php endwhile; ?>
