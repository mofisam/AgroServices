<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit();
}
include '../includes/header.php';
$user_id = $_SESSION['user_id'];

// Fetch registration fee from settings
$stmt = $conn->prepare("SELECT registration_fee FROM settings LIMIT 1");
$stmt->execute();
$stmt->bind_result($registration_fee);
$stmt->fetch();
$stmt->close();

$reference = 'RNW' . time() . rand(1000, 9999);
$paystack_public_key = 'pk_test_3d8772ab51c1407f1302d2fffc114220b0b1d9ee';

$_SESSION['renewal'] = [
    'user_id' => $user_id,
    'amount' => $registration_fee * 100, // kobo
    'reference' => $reference
];
?>

<div class="container py-5">
    <h2 class="mb-4">🔄 Business Renewal</h2>

    <div class="card p-4 shadow-sm">
        <h5 class="mb-3">Renewal Fee: ₦<?= number_format($registration_fee, 2) ?></h5>
        <form id="paymentForm">
            <button type="button" onclick="payWithPaystack()" class="btn btn-primary w-100">💳 Proceed to Payment</button>
        </form>
    </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
    function payWithPaystack() {
        const handler = PaystackPop.setup({
            key: '<?= $paystack_public_key ?>',
            email: '<?= $_SESSION['email'] ?>',
            amount: <?= $_SESSION['renewal']['amount'] ?>,
            ref: '<?= $reference ?>',
            callback: function(response) {
                window.location.href = "verify_renewal.php?reference=" + response.reference;
            },
            onClose: function() {
                alert('Payment was not completed.');
            }
        });
        handler.openIframe();
    }
</script>

<?php include '../includes/footer.php'; ?>