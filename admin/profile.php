<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'admin') {
    header("Location: ../login");
    exit();
}

$user_id = $_SESSION["user_id"];

// Get admin details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Format dates
$join_date = date('F j, Y', strtotime($admin["created_at"]));
$last_login = $admin["last_login"] ? date('F j, Y \a\t h:i A', strtotime($admin["last_login"])) : 'Never';

// Set profile picture
$profile_pic = !empty($admin["profile_picture"]) ? 
    "../uploads/profile_pics/" . $admin["profile_picture"] : 
    "../assets/images/default-admin.png";

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <!-- Admin Profile Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="position-relative mb-3">
                        <img src="<?= htmlspecialchars($profile_pic) ?>" 
                             class="rounded-circle shadow" 
                             width="150" height="150" 
                             style="object-fit: cover; border: 3px solid #fff; box-shadow: 0 2px 15px rgba(0,0,0,0.1);">
                        <a href="edit_profile" class="btn btn-sm btn-danger rounded-circle position-absolute bottom-0 end-0" 
                           style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                    
                    <h4 class="mb-1"><?= htmlspecialchars($admin["first_name"] . " " . $admin["last_name"]) ?></h4>
                    <p class="text-muted mb-3">System Administrator</p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <span class="badge bg-danger">
                            <i class="bi bi-shield-lock me-1"></i> Admin
                        </span>
                        <span class="badge bg-<?= $admin['status'] == 'active' ? 'success' : 'secondary' ?>">
                            <?= htmlspecialchars(ucfirst($admin["status"])) ?>
                        </span>
                        <span class="badge bg-<?= $admin['is_verified'] ? 'success' : 'warning' ?>">
                            <?= $admin['is_verified'] ? 'Verified' : 'Unverified' ?>
                        </span>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="edit_profile" class="btn btn-outline-danger">
                            <i class="bi bi-pencil-square me-1"></i> Edit Profile
                        </a>
                        <a href="change_password" class="btn btn-outline-dark">
                            <i class="bi bi-shield-lock me-1"></i> Change Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Personal Information -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i> Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small mb-1">Full Name</label>
                                <p class="mb-0"><?= htmlspecialchars($admin["first_name"]) . " " . htmlspecialchars($admin["last_name"]) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small mb-1">Email</label>
                                <p class="mb-0"><?= htmlspecialchars($admin["email"]) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small mb-1">Phone</label>
                                <p class="mb-0"><?= !empty($admin["phone"]) ? htmlspecialchars($admin["phone"]) : 'Not provided' ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small mb-1">Gender</label>
                                <p class="mb-0"><?= !empty($admin["sex"]) ? htmlspecialchars($admin["sex"]) : 'Not specified' ?></p>
                            </div>
                        </div>
                        
                        <!-- Address Information -->
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label text-muted small mb-1">Address</label>
                                <p class="mb-0"><?= !empty($admin["address"]) ? htmlspecialchars($admin["address"]) : 'Not provided' ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small mb-1">State</label>
                                <p class="mb-0"><?= !empty($admin["state"]) ? htmlspecialchars($admin["state"]) : 'Not specified' ?></p>
                            </div>
                        </div>
                        
                        <!-- Account Information -->
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
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>