<?php
include '../includes/header.php';

// Check if payment was actually successful (security measure)
if (!isset($_SESSION['payment_success'])) {
    header("Location: ../403.php");
    exit();
}

// Clear the payment success flag
unset($_SESSION['payment_success']);
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <!-- Success Card -->
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body p-5">
                    <!-- Animated Checkmark -->
                    <div class="mb-4">
                        <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52" width="80" height="80">
                            <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" stroke="#28a745" stroke-width="3"/>
                            <path class="checkmark__check" fill="none" stroke="#28a745" stroke-width="4" stroke-linecap="round" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                        </svg>
                    </div>
                    
                    <h2 class="mb-3">🎉 Payment Successful!</h2>
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

<style>
    /* Animated checkmark styles */
    .checkmark__circle {
        stroke-dasharray: 166;
        stroke-dashoffset: 166;
        stroke-width: 3;
        stroke-miterlimit: 10;
        stroke: #28a745;
        fill: none;
        animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }
    
    .checkmark__check {
        transform-origin: 50% 50%;
        stroke-dasharray: 48;
        stroke-dashoffset: 48;
        animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
    }
    
    @keyframes stroke {
        100% {
            stroke-dashoffset: 0;
        }
    }
</style>
<?php include '../includes/footer.php'; ?>