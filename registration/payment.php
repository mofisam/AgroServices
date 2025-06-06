<?php
session_start();
include '../config/db.php';
include '../includes/email_template.php';

if (!isset($_GET['user_id'])) {
    die("Invalid request!");
}

$user_id = intval($_GET['user_id']);

// 🔍 **Fetch Registration Fee**
$fee_stmt = $conn->prepare("SELECT registration_fee FROM settings LIMIT 1");
$fee_stmt->execute();
$fee_stmt->bind_result($registration_fee);
$fee_stmt->fetch();
$fee_stmt->close();

// 🔍 **Fetch Seller Email**
$email_stmt = $conn->prepare("SELECT email, first_name FROM users WHERE id = ?");
$email_stmt->bind_param("i", $user_id);
$email_stmt->execute();
$email_stmt->bind_result($email, $first_name);
$email_stmt->fetch();
$email_stmt->close();

// Generate unique reference
$reference = "AGRO_" . time() . rand(1000, 9999);

// 📝 Store checkout information in the session
$_SESSION['checkout'] = [
    'user_id' => $user_id,
    'email' => $email,
    'amount' => $registration_fee * 100, // Paystack uses kobo
    'reference' => $reference
];

// ✅ **Paystack Keys**
$paystack_public_key = PAYSTACK_PUBLIC; // Replace with your public key
?>

<!-- ✅ **Payment Confirmation Form** -->
<div class="container py-5">
    <h2 class="mb-4">💳 Complete Your Payment</h2>
    <p>Pay ₦<?= number_format($registration_fee, 2); ?> to activate your seller account.</p>

    <button id="paystackBtn" class="btn btn-success">Pay with Paystack</button>
</div>

<!-- ✅ **Paystack Script** -->
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
    document.getElementById('paystackBtn').addEventListener('click', function () {
        const handler = PaystackPop.setup({
            key: '<?= $paystack_public_key ?>',
            email: '<?= $email ?>',
            amount: <?= $registration_fee * 100 ?>,
            reference: '<?= $reference ?>',
            callback: function (response) {
                window.location.href = "verify_payment.php?reference=" + response.reference;
            },
            onClose: function () {
                alert('Payment window closed.');
            }
        });
        handler.openIframe();
    });
</script>