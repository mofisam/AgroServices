<?php
include '../config/db.php';

$ref = $_POST['ref'];

$stmt = $conn->prepare("UPDATE orders SET payment_status = 'paid' WHERE payment_reference = ?");
$stmt->bind_param("s", $ref);
$stmt->execute();

echo "<h3>✅ Payment Successful!</h3>";
echo "<a href='../orders/buyer_orders'>View My Orders</a>";
