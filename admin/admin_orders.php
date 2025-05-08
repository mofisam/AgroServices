<?php
session_start();
include '../config/db.php';

if ($_SESSION['role'] !== 'admin') exit("Unauthorized");

$orders = $conn->query("SELECT o.*, u.first_name, u.last_name FROM orders o 
                        JOIN users u ON o.buyer_id = u.id 
                        ORDER BY o.created_at DESC");
?>

<h2>🛡 Admin Order Dashboard</h2>
<?php while ($o = $orders->fetch_assoc()): ?>
    <div class="border p-3 mb-3">
        <strong>Order #<?= $o['id'] ?> (<?= $o['payment_status'] ?>)</strong><br>
        Buyer: <?= $o['first_name'] . " " . $o['last_name'] ?><br>
        Total: ₦<?= number_format($o['total_amount'],2) ?><br>
        Status: <strong><?= strtoupper($o['status']) ?></strong><br>
        Date: <?= $o['created_at'] ?><br>
        <a href="../orders/view_order.php?id=<?= $o['id'] ?>" class="btn btn-outline-primary btn-sm">View Details</a>
    </div>
<?php endwhile; ?>
