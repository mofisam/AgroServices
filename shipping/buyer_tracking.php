<?php
session_start();
include '../config/db.php';

$order_id = $_GET['id'];
$uid = $_SESSION['user_id'];

$order = $conn->prepare("SELECT * FROM orders WHERE id = ? AND buyer_id = ?");
$order->bind_param("ii", $order_id, $uid);
$order->execute();
$o = $order->get_result()->fetch_assoc();
?>

<h3>🚚 Order Tracking #<?= $o['id'] ?></h3>

<p><strong>Status:</strong> <?= strtoupper($o['status']) ?></p>
<p><strong>Courier:</strong> <?= $o['courier_name'] ?? 'Pending' ?></p>
<p><strong>Contact:</strong> <?= $o['courier_contact'] ?? 'N/A' ?></p>
<p><strong>Delivery Note:</strong><br><?= nl2br($o['delivery_note'] ?? 'No notes yet.') ?></p>

<?php if ($o['status'] == 'shipped'): ?>
    <div class="alert alert-success">Your order is on its way 🚚</div>
<?php elseif ($o['status'] == 'completed'): ?>
    <div class="alert alert-info">Your order has been delivered ✅</div>
<?php endif; ?>
