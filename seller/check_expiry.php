<?php
include '../config/db.php';
include 'email_function.php'; // Include sendEmail function

$today = date("Y-m-d");
$reminder_date = date("Y-m-d", strtotime("+7 days"));

$stmt = $conn->prepare("SELECT u.email, b.business_name, b.payment_expiry FROM users u JOIN business_accounts b ON u.id = b.user_id WHERE u.role = 'seller' AND b.payment_expiry = ?");
$stmt->bind_param("s", $reminder_date);
$stmt->execute();
$result = $stmt->get_result();

while ($seller = $result->fetch_assoc()) {
    $email = $seller["email"];
    $business_name = $seller["business_name"];
    $expiry_date = $seller["payment_expiry"];

    $subject = "Renew Your Seller Subscription - AgroServices";
    $message = "<h3>Dear Seller,</h3><p>Your subscription for <b>{$business_name}</b> is expiring on {$expiry_date}. Please renew to continue selling.
    Please renew to continue selling.</p>
        <p><a href='https://fjjjf.com/renew_payment.php'>Click here to renew</a></p>";
    sendEmail($email, $subject, $message);
}

$stmt->close();
?>