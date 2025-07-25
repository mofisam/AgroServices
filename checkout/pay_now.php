<?php
include '../config/db.php';
include '../config/.env'; // contains PAYSTACK_PUBLIC
session_start();
include_once 'includes/tracking.php';

$ref = $_GET['ref'];
$amount = $_GET['amount'] * 100; // Paystack accepts kobo
$email = $_SESSION['user_email']; // Ensure user is logged in
?>

<script src="https://js.paystack.co/v1/inline.js"></script>

<h2>💳 Pay with Paystack</h2>
<p>Amount: ₦<?= number_format($amount / 100, 2) ?></p>
<button id="payBtn" class="btn btn-success">Proceed to Paystack</button>

<script>
document.getElementById("payBtn").addEventListener("click", function () {
    var handler = PaystackPop.setup({
        key: "<?= PAYSTACK_PUBLIC ?>",
        email: "<?= $email ?>",
        amount: <?= $amount ?>,
        ref: "<?= $ref ?>",
        callback: function(response) {
            window.location.href = "verify?reference=" + response.reference;
        },
        onClose: function() {
            alert('💡 Payment window closed.');
        }
    });
    handler.openIframe();
});
</script>
