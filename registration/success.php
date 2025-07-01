<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

// Check if we have a reference and valid session
if (!isset($_GET['ref'])) {
    header("Location: ../403.php");
    exit();
}

$reference = $_GET['ref'];

// Verify the payment exists in database
$stmt = $conn->prepare("SELECT * FROM business_payment_records WHERE reference = ?");
$stmt->bind_param("s", $reference);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../403.php");
    exit();
}

$payment = $result->fetch_assoc();
$stmt->close();

// Get user information
$user_stmt = $conn->prepare("SELECT first_name FROM users WHERE id = ?");
$user_stmt->bind_param("i", $payment['user_id']);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <!-- Success Card -->
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body p-5">
                    <!-- Success Icon -->
                    <div class="mb-4">
                        <div class="bg-success bg-opacity-10 d-inline-flex p-4 rounded-circle">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                        </div>
                    </div>
                    
                    <h2 class="mb-3">🎉 Payment Successful!</h2>
                    <?php if(isset($user['first_name'])): ?>
                    <p class="lead text-muted mb-4">Congratulations <?= htmlspecialchars($user['first_name']) ?>!</p>
                    <?php endif; ?>
                    <p class="lead text-muted mb-4">Your registration fee has been processed successfully.</p>
                    
                    <div class="alert alert-success mb-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-3 fs-4"></i>
                            <div>
                                <h5 class="alert-heading mb-1">Business Account Activated</h5>
                                <p class="mb-0">You can now start selling on our platform</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Next Steps -->
                    <div class="card bg-light border-0 mb-4 text-start">
                        <div class="card-body">
                            <h5 class="mb-3"><i class="bi bi-list-check me-2"></i> Next Steps</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item bg-transparent d-flex align-items-center">
                                    <i class="bi bi-check-circle text-success me-3"></i>
                                    <span>Complete your business profile</span>
                                </li>
                                <li class="list-group-item bg-transparent d-flex align-items-center">
                                    <i class="bi bi-check-circle text-success me-3"></i>
                                    <span>Add your first product</span>
                                </li>
                                <li class="list-group-item bg-transparent d-flex align-items-center">
                                    <i class="bi bi-check-circle text-success me-3"></i>
                                    <span>Set up your payment methods</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-grid gap-3">
                        <a href="../seller/dashboard" class="btn btn-primary py-3">
                            <i class="bi bi-speedometer2 me-2"></i> Go to Dashboard
                        </a>
                        <a href="../seller/add_product" class="btn btn-outline-primary">
                            <i class="bi bi-plus-circle me-2"></i> Add Your First Product
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>