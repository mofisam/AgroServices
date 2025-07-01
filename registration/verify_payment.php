<?php
session_start();
include '../config/db.php';
include '../includes/email_template.php';

if (!isset($_GET['reference']) || !isset($_SESSION['checkout'])) {
    die("Unauthorized access.");
}
$reference = $_GET['reference'];
$checkout = $_SESSION['checkout'];
$paystack_secret_key = PAYSTACK_SECRET; // Replace with your secret key

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
$checkout_amount = (int) $checkout['amount'];
$checkout_email = $checkout['email'];
$user_id = $checkout['user_id'];
$payment_type = $checkout['type'] ?? 'registration';

// 🛡️ **Amount Check**
if ($paid_amount !== $checkout_amount) {
    die("Payment data mismatch. Amount is incorrect. Paid: $paid_amount, Expected: $checkout_amount");
}

// 🛡️ **Email Check**
if ($email_from_paystack !== $checkout_email) {
    die("Payment data mismatch. Email is incorrect. Paystack: $email_from_paystack, Expected: $checkout_email");
}

// ✅ **Update Database**
$payment_status = "paid";
$payment_expiry = date("Y-m-d", strtotime("+1 year"));
$amount_in_naira = $paid_amount / 100;

// 🔄 **Begin Transaction**
$conn->begin_transaction();

try {
    // ✅ **1️⃣ Update Business Account**
    $stmt = $conn->prepare("UPDATE business_accounts 
                            SET payment_status = ?, payment_expiry = ?, registration_status = 'approved' 
                            WHERE user_id = ?");
    $stmt->bind_param("ssi", $payment_status, $payment_expiry, $user_id);
    $stmt->execute();
    $stmt->close();

    // ✅ **2️⃣ Record in Payment History**
    $insert = $conn->prepare("INSERT INTO business_payment_records (user_id, amount, type, reference, status)
                              VALUES (?, ?, ?, ?, ?)");
    $status = 'success';
    $insert->bind_param("idsss", $user_id, $amount_in_naira, $payment_type, $reference, $status);
    $insert->execute();
    $insert->close();

    // ✅ **3️⃣ Commit Transaction**
    $conn->commit();

    // ✅ **Send Confirmation Email**
    $subject = "Payment Confirmation - F and V Agro Services";
    $message = "
    <h3>Dear {$checkout['email']},</h3>
    <p>Your payment of ₦" . number_format($amount_in_naira, 2) . " has been successfully received.</p>
    <p>Your business is now active and valid until <strong>$payment_expiry</strong>.</p>
    <p>Thank you for choosing F and V Agro Services.</p>
    ";

    sendEmail($checkout['email'], "Payment Confirmation - F and V Agro Services", $message);


    if (!sendEmail($checkout['email'], $subject, $message)) {
        error_log("Failed to send confirmation email to " . $checkout['email']);
    }

    // ✅ **Clear Session and Redirect**
    unset($_SESSION['checkout']);
    $_SESSION['payment_success'] = true;
    header("Location: success?ref=" . urlencode($reference));
    exit();
    
} catch (Exception $e) {
    $conn->rollback();
    die("Database update failed: " . $e->getMessage());
}
?>