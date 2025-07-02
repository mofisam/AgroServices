<?php
session_start();
include '../config/db.php';


if (!isset($_SESSION["user_id"])) {
    header("Location: login");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";

// Function to sanitize input
function sanitizeInput($data, $type = 'string') {
    $data = trim($data);
    $data = stripslashes($data);
    
    switch($type) {
        case 'email':
            return filter_var($data, FILTER_SANITIZE_EMAIL);
        case 'int':
            return filter_var($data, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        case 'url':
            return filter_var($data, FILTER_SANITIZE_URL);
        case 'string':
        default:
            return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = ["type" => "danger", "text" => "Invalid CSRF token"];
    } else {
        // Input sanitization and validation
        $first_name = sanitizeInput($_POST["first_name"]);
        $last_name = sanitizeInput($_POST["last_name"]);
        $phone = sanitizeInput($_POST["phone"], 'int');
        $address = sanitizeInput($_POST["address"]);
        $state = sanitizeInput($_POST["state"]);
        $sex = in_array($_POST["sex"], ['Male', 'Female']) ? $_POST["sex"] : '';

        // Additional validation
        if (empty($first_name) || empty($last_name) || empty($phone) || empty($address) || empty($state) || empty($sex)) {
            $message = ["type" => "danger", "text" => "All fields are required"];
        } elseif (!preg_match('/^[a-zA-Z\s\-]{2,50}$/', $first_name) || !preg_match('/^[a-zA-Z\s\-]{2,50}$/', $last_name)) {
            $message = ["type" => "danger", "text" => "Invalid name format"];
        } elseif (!preg_match('/^[0-9\+]{10,15}$/', $phone)) {
            $message = ["type" => "danger", "text" => "Invalid phone number"];
        } else {
            $update_pic = "";
            $profile_picture = null;

            // Handle profile picture upload
            if (isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] === UPLOAD_ERR_OK) {
                $target_dir = "../uploads/profile_pics/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0755, true); // More secure permissions
                }
                
                $file_ext = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
                $allowed_types = ["jpg", "jpeg", "png", "webp"];
                $max_file_size = 2 * 1024 * 1024; // 2MB
                
                // Validate file
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($_FILES["profile_picture"]["tmp_name"]);
                $valid_mimes = [
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'webp' => 'image/webp'
                ];
                
                if (!in_array($file_ext, $allowed_types) || !in_array($mime, $valid_mimes)) {
                    $message = ["type" => "danger", "text" => "Invalid file type"];
                } elseif ($_FILES["profile_picture"]["size"] > $max_file_size) {
                    $message = ["type" => "danger", "text" => "File too large. Max 2MB allowed"];
                } else {
                    // Delete old profile picture if exists
                    $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $stmt->bind_result($old_picture);
                    $stmt->fetch();
                    $stmt->close();
                    
                    if ($old_picture && file_exists("../uploads/profile_pics/" . $old_picture)) {
                        unlink("../uploads/profile_pics/" . $old_picture);
                    }

                    $profile_picture = bin2hex(random_bytes(8)) . "." . $file_ext;
                    $target_file = $target_dir . $profile_picture;
                    
                    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                        $update_pic = ", profile_picture=?";
                        
                        // Compress image
                        try {
                            if ($file_ext == "jpg" || $file_ext == "jpeg") {
                                $image = imagecreatefromjpeg($target_file);
                                imagejpeg($image, $target_file, 85);
                                imagedestroy($image);
                            } elseif ($file_ext == "png") {
                                $image = imagecreatefrompng($target_file);
                                imagepalettetotruecolor($image);
                                imagealphablending($image, true);
                                imagesavealpha($image, true);
                                imagepng($image, $target_file, 6);
                                imagedestroy($image);
                            } elseif ($file_ext == "webp") {
                                $image = imagecreatefromwebp($target_file);
                                imagewebp($image, $target_file, 85);
                                imagedestroy($image);
                            }
                        } catch (Exception $e) {
                            error_log("Image processing error: " . $e->getMessage());
                        }
                    }
                }
            }

            // Update user data if no errors
            if (empty($message)) {
                $sql = "UPDATE users SET first_name=?, last_name=?, phone=?, address=?, state=?, sex=? $update_pic WHERE id=?";
                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    if ($profile_picture !== null) {
                        $stmt->bind_param("sssssssi", $first_name, $last_name, $phone, $address, $state, $sex, $profile_picture, $user_id);
                    } else {
                        $stmt->bind_param("ssssssi", $first_name, $last_name, $phone, $address, $state, $sex, $user_id);
                    }
                    
                    if ($stmt->execute()) {
                        $message = ["type" => "success", "text" => "Profile updated successfully!"];
                    } else {
                        $message = ["type" => "danger", "text" => "Error updating profile"];
                    }
                    $stmt->close();
                } else {
                    $message = ["type" => "danger", "text" => "Database error"];
                }
            }
        }
    }
}

// Generate new CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Get current user data
$stmt = $conn->prepare("SELECT first_name, last_name, phone, address, state, sex, profile_picture FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($first_name, $last_name, $phone, $address, $state, $sex, $profile_picture);
$stmt->fetch();
$stmt->close();

include '../includes/header.php';
?>

<!-- Main Container -->
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Profile Settings</h1>
                <a href="profile" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Profile
                </a>
            </div>

            <!-- Profile Update Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="bi bi-person-gear me-2"></i> Update Your Information</h5>
                </div>
                
                <div class="card-body p-4">
                    <!-- Feedback Message -->
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?= htmlspecialchars($message['type']) ?> alert-dismissible fade show mb-4" role="alert">
                            <?= htmlspecialchars($message['text']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Profile Update Form -->
                    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        
                        <!-- Profile Picture Section -->
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <img id="profileImagePreview" src="<?= !empty($profile_picture) ? '../uploads/profile_pics/' . htmlspecialchars($profile_picture) : 'assets/images/default-profile.png' ?>" 
                                     class="rounded-circle shadow" width="150" height="150" style="object-fit: cover; border: 3px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                <label for="profile_picture" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0" style="width: 40px; height: 40px;">
                                    <i class="bi bi-camera"></i>
                                    <input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png,image/webp" class="d-none">
                                </label>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">JPG, PNG or WEBP. Max 2MB</small>
                            </div>
                        </div>

                        <!-- Personal Information Section -->
                        <div class="mb-4">
                            <h6 class="mb-3 pb-2 border-bottom"><i class="bi bi-person-vcard me-2"></i> Personal Information</h6>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?= htmlspecialchars($first_name) ?>" required pattern="[a-zA-Z\s\-]{2,50}">
                                    <div class="invalid-feedback">Please enter a valid first name (2-50 characters, letters only)</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?= htmlspecialchars($last_name) ?>" required pattern="[a-zA-Z\s\-]{2,50}">
                                    <div class="invalid-feedback">Please enter a valid last name (2-50 characters, letters only)</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="sex" class="form-label">Gender</label>
                                    <select class="form-select" id="sex" name="sex" required>
                                        <option value="">Select gender</option>
                                        <option value="Male" <?= $sex === "Male" ? "selected" : "" ?>>Male</option>
                                        <option value="Female" <?= $sex === "Female" ? "selected" : "" ?>>Female</option>
                                    </select>
                                    <div class="invalid-feedback">Please select your gender</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?= htmlspecialchars($phone) ?>" required pattern="[0-9\+]{10,15}">
                                    <div class="invalid-feedback">Please enter a valid phone number (10-15 digits)</div>
                                </div>
                            </div>
                        </div>

                        <!-- Address Information Section -->
                        <div class="mb-4">
                            <h6 class="mb-3 pb-2 border-bottom"><i class="bi bi-geo-alt me-2"></i> Address Information</h6>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Full Address</label>
                                <input type="text" class="form-control" id="address" name="address" 
                                       value="<?= htmlspecialchars($address) ?>" required>
                                <div class="invalid-feedback">Please enter your address</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="state" class="form-label">State</label>
                                <select class="form-select" id="state" name="state" required>
                                    <option value="">Select your state</option>
                                    <?php
                                    $nigerian_states = [
                                        "Abia", "Adamawa", "Akwa Ibom", "Anambra", "Bauchi", "Bayelsa", "Benue", "Borno", 
                                        "Cross River", "Delta", "Ebonyi", "Edo", "Ekiti", "Enugu", "Gombe", "Imo", "Jigawa", 
                                        "Kaduna", "Kano", "Katsina", "Kebbi", "Kogi", "Kwara", "Lagos", "Nasarawa", "Niger", 
                                        "Ogun", "Ondo", "Osun", "Oyo", "Plateau", "Rivers", "Sokoto", "Taraba", "Yobe", 
                                        "Zamfara", "FCT Abuja"
                                    ];
                                    foreach ($nigerian_states as $state_option) {
                                        $selected = ($state_option === $state) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($state_option) . "' $selected>$state_option</option>";
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">Please select your state</div>
                            </div>
                        </div>

                        <!-- Form Submission -->
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-save me-2"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for enhanced functionality -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Form validation
(function () {
    'use strict'
    const forms = document.querySelectorAll('.needs-validation')
    Array.from(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})();

// Profile picture preview
document.getElementById('profile_picture').addEventListener('change', function(e) {
    const preview = document.getElementById('profileImagePreview');
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(this.files[0]);
    }
});

// Phone number formatting
document.getElementById('phone').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9+]/g, '');
});
</script>

<?php include '../includes/footer.php'; ?>