<?php
session_start();
include '../config/db.php';

$user_id = $_SESSION["user_id"];

// Get payment details
$stmt = $conn->prepare("SELECT b.payment_status, b.payment_expiry FROM business_accounts b WHERE b.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($payment_status, $payment_expiry);
$stmt->fetch();
$stmt->close();

// Redirect to Paystack if expired
if ($payment_status !== "paid" || strtotime($payment_expiry) < time()) {
    header("Location: paystack.php");
    exit();
}

echo "✅ Your subscription is active until " . $payment_expiry;
?>
