<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login");
    exit();
}
include_once 'includes/tracking.php';

$user_id = $_SESSION["user_id"];

// Get user details with additional statistics
$stmt = $conn->prepare("SELECT 
    u.*, 
    (SELECT COUNT(*) FROM orders WHERE buyer_id = u.id) as order_count,
    (SELECT COUNT(*) FROM product_reviews WHERE user_id = u.id) as review_count,
    (SELECT COUNT(*) FROM wishlists WHERE user_id = u.id) as wishlist_count,
    b.business_name, b.payment_status
    FROM users u
    LEFT JOIN business_accounts b ON b.user_id = u.id
    WHERE u.id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Format dates
$join_date = date('F j, Y', strtotime($user["created_at"]));
$last_login = $user["last_login"] ? date('F j, Y \a\t h:i A', strtotime($user["last_login"])) : 'Never';

// Set default profile picture if none exists
$profile_pic = !empty($user["profile_picture"]) ? 
    "uploads/profile_pics/" . $user["profile_picture"] : 
    "assets/images/default-profile.png";

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <!-- Left Sidebar -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="position-relative mb-3">
                        <img src="<?= htmlspecialchars($profile_pic) ?>" 
                             class="rounded-circle shadow" 
                             width="150" height="150" 
                             style="object-fit: cover; border: 3px solid #fff; box-shadow: 0 2px 15px rgba(0,0,0,0.1);">
                        <a href="edit_profile" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0" 
                           style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                    
                    <h4 class="mb-1"><?= htmlspecialchars($user["first_name"] . " " . $user["last_name"]) ?></h4>
                    <p class="text-muted mb-3">@<?= htmlspecialchars(strtolower($user["first_name"])) ?></p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <span class="badge bg-<?= $user['role'] == 'admin' ? 'danger' : ($user['role'] == 'seller' ? 'info' : 'primary') ?>">
                            <?= htmlspecialchars(ucfirst($user["role"])) ?>
                        </span>
                        <span class="badge bg-<?= $user['status'] == 'active' ? 'success' : 'secondary' ?>">
                            <?= htmlspecialchars(ucfirst($user["status"])) ?>
                        </span>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="edit_profile" class="btn btn-outline-primary">
                            <i class="bi bi-pencil-square me-1"></i> Edit Profile
                        </a>
                        <a href="change_password" class="btn btn-outline-secondary">
                            <i class="bi bi-shield-lock me-1"></i> Change Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i> Profile Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small mb-1">Full Name</label>
                                <p class="mb-0"><?= htmlspecialchars($user["first_name"] . " " . $user["last_name"]) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small mb-1">Email</label>
                                <p class="mb-0"><?= htmlspecialchars($user["email"]) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small mb-1">Phone</label>
                                <p class="mb-0"><?= !empty($user["phone"]) ? htmlspecialchars($user["phone"]) : 'Not provided' ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small mb-1">Gender</label>
                                <p class="mb-0"><?= !empty($user["sex"]) ? htmlspecialchars($user["sex"]) : 'Not specified' ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small mb-1">Address</label>
                                <p class="mb-0"><?= !empty($user["address"]) ? htmlspecialchars($user["address"]) : 'Not provided' ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small mb-1">State</label>
                                <p class="mb-0"><?= !empty($user["state"]) ? htmlspecialchars($user["state"]) : 'Not specified' ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small mb-1">Member Since</label>
                                <p class="mb-0"><?= $join_date ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small mb-1">Last Login</label>
                                <p class="mb-0"><?= $last_login ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($user["role"] === "seller"): ?>
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="mb-3"><i class="bi bi-shop me-2"></i> Business Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Business Name</label>
                                    <p class="mb-0"><?= !empty($user["business_name"]) ? htmlspecialchars($user["business_name"]) : 'Not registered' ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Account Status</label>
                                    <p class="mb-0">
                                        <span class="badge bg-<?= $user['payment_status'] == 'active' ? 'success' : ($user['payment_status'] == 'expired' ? 'danger' : 'secondary') ?>">
                                            <?= !empty($user["payment_status"]) ? htmlspecialchars(ucfirst($user["payment_status"])) : 'Not specified' ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- User Stats -->
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="display-5 text-primary mb-1"><?= $user['order_count'] ?></div>
                            <div class="text-muted">Orders</div>
                            <a href="orders" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="display-5 text-info mb-1"><?= $user['review_count'] ?></div>
                            <div class="text-muted">Reviews</div>
                            <a href="reviews" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="display-5 text-warning mb-1"><?= $user['wishlist_count'] ?></div>
                            <div class="text-muted">Wishlist</div>
                            <a href="wishlist" class="stretched-link"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>