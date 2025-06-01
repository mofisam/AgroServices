<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Input sanitization and validation
    $first_name = trim(filter_input(INPUT_POST, "first_name", FILTER_SANITIZE_STRING));
    $last_name = trim(filter_input(INPUT_POST, "last_name", FILTER_SANITIZE_STRING));
    $phone = trim(filter_input(INPUT_POST, "phone", FILTER_SANITIZE_STRING));
    $address = trim(filter_input(INPUT_POST, "address", FILTER_SANITIZE_STRING));
    $state = trim(filter_input(INPUT_POST, "state", FILTER_SANITIZE_STRING));
    $sex = trim(filter_input(INPUT_POST, "sex", FILTER_SANITIZE_STRING));

    $update_pic = "";
    $profile_picture = null;

    // Handle profile picture upload
    if (isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/profile_pics/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
        $allowed_types = ["jpg", "jpeg", "png", "webp"];
        
        if (in_array($file_ext, $allowed_types)) {
            // Delete old profile picture if exists
            $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($old_picture);
            $stmt->fetch();
            $stmt->close();
            
            if ($old_picture && file_exists("uploads/profile_pics/" . $old_picture)) {
                unlink("uploads/profile_pics/" . $old_picture);
            }

            $profile_picture = uniqid("profile_") . "." . $file_ext;
            $target_file = $target_dir . $profile_picture;
            
            // Resize and optimize image
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $update_pic = ", profile_picture=?";
                 
                // Compress image if needed
                if ($file_ext == "jpg" || $file_ext == "jpeg") {
                    $image = imagecreatefromjpeg($target_file);
                    imagejpeg($image, $target_file, 85); // 85% quality
                    imagedestroy($image);
                } elseif ($file_ext == "png") {
                    $image = imagecreatefrompng($target_file);
                    imagepalettetotruecolor($image);
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                    imagepng($image, $target_file, 6); // Compression level 6
                    imagedestroy($image);
                }
            }
        }
    }

    // Update user data
    $sql = "UPDATE users SET first_name=?, last_name=?, phone=?, address=?, state=?, sex=? $update_pic WHERE id=?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        if ($profile_picture !== null) {
            $stmt->bind_param("ssssssss", $first_name, $last_name, $phone, $address, $state, $sex, $profile_picture, $user_id);
        } else {
            $stmt->bind_param("sssssss", $first_name, $last_name, $phone, $address, $state, $sex, $user_id);
        }
        
        if ($stmt->execute()) {
            $message = ["type" => "success", "text" => "Profile updated successfully!"];
        } else {
            $message = ["type" => "danger", "text" => "Error updating profile: " . $stmt->error];
        }
        $stmt->close();
    } else {
        $message = ["type" => "danger", "text" => "Database error: " . $conn->error];
    }
}

// Get current user data
$stmt = $conn->prepare("SELECT first_name, last_name, phone, address, state, sex, profile_picture FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($first_name, $last_name, $phone, $address, $state, $sex, $profile_picture);
$stmt->fetch();
$stmt->close();

include 'includes/header.php';
?>

<!-- Main Container -->
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Profile Settings</h1>
                <a href="profile.php" class="btn btn-outline-secondary">
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
                        <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show mb-4" role="alert">
                            <?= htmlspecialchars($message['text']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Profile Update Form -->
                    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <!-- Profile Picture Section -->
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <img id="profileImagePreview" src="<?= !empty($profile_picture) ? 'uploads/profile_pics/' . htmlspecialchars($profile_picture) : 'assets/images/default-profile.png' ?>" 
                                     class="rounded-circle shadow" width="150" height="150" style="object-fit: cover; border: 3px solid #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                <label for="profile_picture" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0" style="width: 40px; height: 40px;">
                                    <i class="bi bi-camera"></i>
                                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="d-none">
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
                                           value="<?= htmlspecialchars($first_name) ?>" required>
                                    <div class="invalid-feedback">Please enter your first name</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?= htmlspecialchars($last_name) ?>" required>
                                    <div class="invalid-feedback">Please enter your last name</div>
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
                                           value="<?= htmlspecialchars($phone) ?>" required>
                                    <div class="invalid-feedback">Please enter a valid phone number</div>
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

<?php include 'includes/footer.php'; ?>