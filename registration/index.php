<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard");
    exit();
}
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center mb-5">
        <div class="col-lg-8 text-center">
            <h1 class="display-5 fw-bold mb-3">Join F and V Agro Services</h1>
            <p class="lead text-muted">Select your account type to get started with Nigeria's premier agricultural marketplace</p>
            
        </div>
    </div>

    <div class="row justify-content-center g-4">
        <!-- Buyer Card -->
        <div class="col-md-5 col-lg-4">
            <div class="card border-0 shadow-lg h-100 hover-effect">
                <div class="card-header bg-success text-white text-center py-4">
                    <i class="bi bi-cart-check display-4 mb-3"></i>
                    <h3 class="h4">Buyer Account</h3>
                </div>
                <div class="card-body text-center py-4">
                    <ul class="list-unstyled text-start mb-4">
                        <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i> Access thousands of farm products</li>
                        <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i> Order from trusted sellers</li>
                        <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i> Secure payment options</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i> 24/7 customer support</li>
                    </ul>
                </div>
                <div class="card-footer bg-transparent border-0 text-center pb-4">
                    <a href="buyer" class="btn btn-success btn-lg px-4 py-2">
                        <i class="bi bi-arrow-right-circle me-2"></i> Register as Buyer
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-1 d-flex align-items-center justify-content-center my-4 my-md-0">
            <div class="vr d-none d-md-block" style="height: 300px;"></div>
            <div class="text-muted d-md-none">OR</div>
        </div>

        <!-- Seller Card -->
        <div class="col-md-5 col-lg-4">
            <div class="card border-0 shadow-lg h-100 hover-effect">
                <div class="card-header bg-primary text-white text-center py-4">
                    <i class="bi bi-shop-window display-4 mb-3"></i>
                    <h3 class="h4">Seller Account</h3>
                </div>
                <div class="card-body text-center py-4">
                    <ul class="list-unstyled text-start mb-4">
                        <li class="mb-3"><i class="bi bi-check-circle-fill text-primary me-2"></i> Reach thousands of buyers</li>
                        <li class="mb-3"><i class="bi bi-check-circle-fill text-primary me-2"></i> Manage inventory online</li>
                        <li class="mb-3"><i class="bi bi-check-circle-fill text-primary me-2"></i> Secure payment processing</li>
                        <li class="mb-3"><i class="bi bi-check-circle-fill text-primary me-2"></i> Business analytics dashboard</li>
                    </ul>
                </div>
                <div class="card-footer bg-transparent border-0 text-center pb-4">
                    <a href="seller" class="btn btn-primary btn-lg px-4 py-2">
                        <i class="bi bi-arrow-right-circle me-2"></i> Register as Seller
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center mt-5">
        <div class="col-lg-8 text-center">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                <a href="../login" class="btn btn-outline-secondary">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Already have an account? Login
                </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-effect {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 12px;
    overflow: hidden;
}
.hover-effect:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
.card-header {
    border-radius: 12px 12px 0 0 !important;
}
</style>

<?php include '../includes/footer.php'; ?>