<?php
session_start();
include '../config/db.php';
include '../includes/email_template.php';

// Validate user_id parameter
if (!isset($_GET['user_id']) || !ctype_digit($_GET['user_id'])) {
    header("Location: ../error.php?code=400");
    exit();
}

$user_id = intval($_GET['user_id']);

// Verify the user exists and is a seller
$user_stmt = $conn->prepare("SELECT u.email, u.first_name, b.payment_status 
                           FROM users u 
                           LEFT JOIN business_accounts b ON u.id = b.user_id 
                           WHERE u.id = ? AND u.role = 'seller'");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    header("Location: ../error.php?code=404");
    exit();
}

$user = $user_result->fetch_assoc();
$user_stmt->close();

// Check if already paid
if ($user['payment_status'] === 'active') {
    header("Location: payment_already_completed.php");
    exit();
}

// Get registration fee
$fee_stmt = $conn->prepare("SELECT registration_fee FROM settings LIMIT 1");
$fee_stmt->execute();
$fee_result = $fee_stmt->get_result();
$registration_fee = $fee_result->fetch_assoc()['registration_fee'];
$fee_stmt->close();

// Generate secure reference
$reference = "AGRO_" . bin2hex(random_bytes(5)) . "_" . time();

// Store checkout in session
$_SESSION['checkout'] = [
    'user_id' => $user_id,
    'email' => $user['email'],
    'amount' => $registration_fee * 100, // Paystack uses kobo
    'reference' => $reference,
    'expires' => time() + 1800 // 30-minute expiration
];
?>

<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Payment Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="mb-0"><i class="bi bi-credit-card me-2"></i> Complete Your Registration</h2>
                </div>
                <div class="card-body">
                    <!-- Payment Summary -->
                    <div class="alert alert-info">
                        <h5 class="alert-heading">Hello, <?= htmlspecialchars($user['first_name']) ?>!</h5>
                        <p>To activate your seller account, please complete the registration payment below.</p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3">Payment Summary</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Registration Fee:</span>
                                        <strong>₦<?= number_format($registration_fee, 2) ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Account Type:</span>
                                        <strong>Seller Account</strong>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold">Total Amount:</span>
                                        <span class="fw-bold text-primary">₦<?= number_format($registration_fee, 2) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body">
                                    <h6 class="text-muted mb-3">Payment Methods</h6>
                                    <div class="mb-3">
                                        <button id="paystackBtn" class="btn btn-success w-100 py-3">
                                            <i class="bi bi-credit-card me-2"></i> Pay with Card
                                        </button>
                                    </div>
                                    <div class="text-center text-muted small">
                                        <i class="bi bi-shield-lock me-1"></i> Secure payment powered by Paystack
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Security Info -->
                    <div class="alert alert-light border">
                        <div class="d-flex">
                            <div class="flex-shrink-0 text-success">
                                <i class="bi bi-shield-check fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="alert-heading">Secure Payment</h6>
                                <p class="small mb-0">Your payment information is processed securely. We do not store your credit card details.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Paystack Script -->
<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
document.getElementById('paystackBtn').addEventListener('click', function () {
    const handler = PaystackPop.setup({
        key: '<?= PAYSTACK_PUBLIC ?>',
        email: '<?= htmlspecialchars($user['email']) ?>',
        amount: <?= $registration_fee * 100 ?>,
        reference: '<?= $reference ?>',
        currency: 'NGN',
        metadata: {
            custom_fields: [
                {
                    display_name: "Seller ID",
                    variable_name: "seller_id",
                    value: "<?= $user_id ?>"
                }
            ]
        },
        callback: function (response) {
            window.location.href = "verify_payment.php?reference=" + response.reference;
        },
        onClose: function () {
            // Optional: Add analytics for closed payment window
            console.log('Payment window closed');
        }
    });
    handler.openIframe();
});
</script>

<?php include '../includes/footer.php'; ?>