<?php
session_start();
include '../config/db.php';

$seller_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT o.id, o.created_at, o.status, oi.quantity, oi.price, oi.subtotal, p.name as product, u.first_name, u.last_name
                        FROM order_items oi
                        JOIN orders o ON oi.order_id = o.id
                        JOIN products p ON oi.product_id = p.id
                        JOIN users u ON o.buyer_id = u.id
                        WHERE oi.seller_id = ?
                        ORDER BY o.created_at DESC");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$res = $stmt->get_result();
?>

<h2>🧾 Orders for Your Products</h2>
<?php while ($o = $res->fetch_assoc()): ?>
    <div class="border p-3 mb-2">
        <strong>Order #<?= $o['id'] ?></strong> (<?= $o['status'] ?>)<br>
        <strong>Buyer:</strong> <?= $o['first_name'] . ' ' . $o['last_name'] ?><br>
        <strong>Product:</strong> <?= $o['product'] ?> <br>
        <strong>Qty:</strong> <?= $o['quantity'] ?> × ₦<?= number_format($o['price']) ?> = ₦<?= number_format($o['subtotal']) ?><br>
        <strong>Date:</strong> <?= $o['created_at'] ?>
    </div>
<?php endwhile; ?>


<form method="POST" action="../orders/update_order_status.php" class="row g-2 mt-2">
    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
    <div class="col-md-4">
        <select name="status" class="form-select">
            <option value="pending" <?= $o['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="processing" <?= $o['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
            <option value="shipped" <?= $o['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
            <option value="completed" <?= $o['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
        </select>
    </div>
    <div class="col-md-4">
        <input type="text" name="tracking_number" class="form-control" placeholder="Tracking Number" value="<?= $o['tracking_number'] ?? '' ?>">
    </div>
    <div class="col-md-4">
        <button class="btn btn-sm btn-primary">Update</button>
    </div>
</form>
