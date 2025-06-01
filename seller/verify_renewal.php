<?php
session_start();
include '../config/db.php';
include '../includes/email_template.php';

if (!isset($_GET['reference']) || !isset($_SESSION['renewal'])) {
    die("Unauthorized access.");
}

$reference = $_GET['reference'];
$renewal = $_SESSION['renewal'];
$paystack_secret_key = 'sk_test_41008269e1c6f30a68e89226ebe8bf9628c9e3ae'; // Replace with your secret key

// ✅ **Verify Paystack Transaction**
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $paystack_secret_key",
        "Content-Type: application/json"
    ]
]);
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    die("Curl error: $err");
}

$paystack_data = json_decode($response, true);

// ✅ **Validation Checks**
$paid_amount = (int) $paystack_data['data']['amount'];
$email_from_paystack = $paystack_data['data']['customer']['email'];
$checkout_amount = (int) $renewal['amount'];

if ($paid_amount !== $checkout_amount) {
    die("Payment data mismatch. Amount is incorrect.");
}

// ✅ **Update Database**
$payment_status = "success";
$new_expiry = date("Y-m-d", strtotime("+1 year"));

$conn->begin_transaction();
try {
    // Update the expiry date in business_accounts
    $stmt = $conn->prepare("UPDATE business_accounts 
                            SET payment_status = ?, payment_expiry = DATE_ADD(payment_expiry, INTERVAL 1 YEAR)
                            WHERE user_id = ?");
    $stmt->bind_param("si", $payment_status, $renewal['user_id']);
    $stmt->execute();
    $stmt->close();

    // Log the payment in business_payment_records
    $amount_paid = $paid_amount / 100;  // 🏦 Converted to Naira
    $log_stmt = $conn->prepare("INSERT INTO business_payment_records (user_id, amount, type, reference, status) VALUES (?, ?, 'renewal', ?, ?)");
    $log_stmt->bind_param("idss", $renewal['user_id'], $amount_paid, $reference, $payment_status);
    $log_stmt->execute();
    $log_stmt->close();

    $conn->commit();

    // ✅ **Send Confirmation Email**
    $subject = "Renewal Confirmation - F and V Agro Services";
    $message = emailTemplate("
        <h3>Your renewal payment has been received.</h3>
        <p>Your business registration has been extended until <strong>$new_expiry</strong>.</p>
        <p>Thank you for choosing F and V Agro Services.</p>
    ");
    sendEmail($email_from_paystack, $subject, $message);

    unset($_SESSION['renewal']);
    header("Location: success.php");
    exit();
} catch (Exception $e) {
    $conn->rollback();
    die("Database update failed: " . $e->getMessage());
}
?>
