<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = $error = "";

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Input validation
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters.";
    } else {
        // Fetch current password hash
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        // Verify old password
        if (!password_verify($old_password, $hashed_password)) {
            $error = "Incorrect current password.";
        } else {
            // Update password
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $new_hashed, $user_id);
            if ($update->execute()) {
                $success = "Password changed successfully!";
                header("Location: profile" );
                exit();
            } else {
                $error = "Failed to update password. Please try again.";
            }
            $update->close();
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-success text-white">
                    <h2 class="h4 mb-0"><i class="bi bi-shield-lock me-2"></i>Change Password</h2>
                </div>
                
                <div class="card-body p-4">
                    <?php if ($success): ?>
                        <div class="alert alert-success d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <div><?= htmlspecialchars($success) ?></div>
                        </div>
                    <?php elseif ($error): ?>
                        <div class="alert alert-danger d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div><?= htmlspecialchars($error) ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label for="old_password" class="form-label fw-bold">Current Password</label>
                            <div class="input-group">
                                <input type="password" name="old_password" id="old_password" 
                                       class="form-control form-control-lg" 
                                       placeholder="Enter current password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="new_password" class="form-label fw-bold">New Password</label>
                            <div class="input-group">
                                <input type="password" name="new_password" id="new_password" 
                                       class="form-control form-control-lg" 
                                       placeholder="At least 6 characters" required
                                       pattern=".{6,}">
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text text-muted small mt-1">
                                Password must be at least 6 characters long.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label fw-bold">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" name="confirm_password" id="confirm_password" 
                                       class="form-control form-control-lg" 
                                       placeholder="Re-enter new password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div id="password-match-feedback" class="small mt-1"></div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-key me-2"></i>Update Password
                            </button>
                            <a href="profile" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Back to Profile
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password visibility toggle
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
        const input = this.parentNode.querySelector('input');
        const icon = this.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });
});

// Password confirmation validation
const newPassword = document.getElementById('new_password');
const confirmPassword = document.getElementById('confirm_password');
const feedback = document.getElementById('password-match-feedback');

function validatePasswordMatch() {
    if (newPassword.value && confirmPassword.value) {
        if (newPassword.value === confirmPassword.value) {
            feedback.textContent = "Passwords match!";
            feedback.style.color = "green";
        } else {
            feedback.textContent = "Passwords do not match!";
            feedback.style.color = "red";
        }
    } else {
        feedback.textContent = "";
    }
}

newPassword.addEventListener('input', validatePasswordMatch);
confirmPassword.addEventListener('input', validatePasswordMatch);

// Bootstrap form validation
(function () {
    'use strict'
    
    const forms = document.querySelectorAll('.needs-validation')
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php include '../includes/footer.php'; ?>