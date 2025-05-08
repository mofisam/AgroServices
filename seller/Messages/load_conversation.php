<?php
session_start();
include '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') exit;

$seller_id = $_SESSION['user_id'];
$user_id = $_GET['user_id'];

$msgStmt = $conn->prepare("
    SELECT i.*, u.first_name, u.last_name, p.name AS product_name 
    FROM product_inquiries i
    JOIN users u ON i.user_id = u.id
    JOIN products p ON i.product_id = p.id
    WHERE i.seller_id = ?
      AND (i.user_id = ? OR (i.sender_role = 'seller' AND i.user_id = ?))
    ORDER BY i.created_at ASC
");
$msgStmt->bind_param("iii", $seller_id, $user_id, $user_id);
$msgStmt->execute();
$messages = $msgStmt->get_result();

// 🔄 Mark buyer messages as read
$conn->query("UPDATE product_inquiries SET read_status='read' WHERE seller_id=$seller_id AND user_id=$user_id AND sender_role='buyer' AND read_status='unread'");

while ($msg = $messages->fetch_assoc()):
?>
  <div class="message <?= $msg['sender_role'] === 'seller' ? 'msg-seller' : 'msg-buyer' ?>">
    <strong><?= $msg['sender_role'] === 'seller' ? 'You (Seller)' : htmlspecialchars($msg['first_name']) ?>:</strong>
    <div><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
    <small class="text-muted d-block"><?= $msg['created_at'] ?> | 🛒 <?= htmlspecialchars($msg['product_name']) ?></small>
  </div>
<?php endwhile; ?>
