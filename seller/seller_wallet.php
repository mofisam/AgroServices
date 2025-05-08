<?php
session_start();
include '../config/db.php';

$seller_id = $_SESSION['user_id'];
$wallet = $conn->query("SELECT balance FROM seller_wallets WHERE seller_id = $seller_id")->fetch_assoc();
$transactions = $conn->query("SELECT * FROM wallet_transactions WHERE seller_id = $seller_id ORDER BY created_at DESC");
?>

<h2>💼 My Wallet</h2>
<p><strong>Available Balance:</strong> ₦<?= number_format($wallet['balance'], 2) ?></p>

<table class="table table-bordered">
    <tr>
        <th>Date</th><th>Amount</th><th>Type</th><th>Details</th>
    </tr>
    <?php while ($txn = $transactions->fetch_assoc()): ?>
        <tr>
            <td><?= $txn['created_at'] ?></td>
            <td>₦<?= number_format($txn['amount'], 2) ?></td>
            <td><?= ucfirst($txn['type']) ?></td>
            <td><?= $txn['description'] ?> (Order #<?= $txn['order_id'] ?>)</td>
        </tr>
    <?php endwhile; ?>
</table>
