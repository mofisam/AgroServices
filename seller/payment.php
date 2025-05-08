<?php
session_start();
include '../config/db.php';

if (!isset($_GET['user_id'])) {
    die("Invalid request!");
}
$user_id = intval($_GET['user_id']);

$fee_stmt = $conn->prepare("SELECT registration_fee FROM settings LIMIT 1");
$fee_stmt->execute();
$fee_stmt->bind_result($registration_fee);
$fee_stmt->fetch();
$fee_stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $payment_status = "paid";
    $payment_expiry = date("Y-m-d", strtotime("+1 year"));

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update and Approve business_accounts table
        $stmt = $conn->prepare("UPDATE business_accounts SET payment_status = ?, payment_expiry = ? registration_status = 'approved' WHERE user_id = ?");
        $stmt->bind_param("ssi", $payment_status, $payment_expiry, $user_id);
        $stmt->execute();
        $stmt->close();


        $conn->commit();

        // Redirect to seller dashboard
        header("Location: seller_dashboard.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "❌ Payment error: " . $e->getMessage();
    }
}

?>

<h2>Complete Payment</h2>
<p>Pay ₦<?php echo number_format($registration_fee, 2); ?> to activate your seller account.</p>

<form method="post">
    <button type="submit">Confirm Payment</button>
</form>

<?php
$subject = "Payment Confirmation - AgroServices";
$message = "<h3>Dear Seller,</h3><p>Your payment of ₦{$registration_fee} has been received. Your business is now active until {$payment_expiry}.</p><p>Thank you for choosing AgroServices!</p>";

sendEmail($email, $subject, $message);
?>