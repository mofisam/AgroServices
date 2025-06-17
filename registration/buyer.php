<?php
session_start();
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

include '../config/db.php';
include_once '../config/.env';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables
$errors = [];
$input_values = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'state' => '',
    'sex' => '',
];

// Registration logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = "Invalid CSRF token!";
    } else {
        // Sanitize inputs
        $input_values = array_map('trim', $_POST);
        $email = filter_var($input_values['email'], FILTER_SANITIZE_EMAIL);
        $phone = preg_replace('/[^0-9]/', '', $input_values['phone']);
        
        // Validate inputs
        if (empty($input_values['first_name'])) $errors[] = "First name is required";
        if (strlen($input_values['first_name']) > 50) $errors[] = "First name too long";
        
        if (empty($input_values['last_name'])) $errors[] = "Last name is required";
        if (strlen($input_values['last_name']) > 50) $errors[] = "Last name too long";
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        } elseif (strlen($email) > 100) {
            $errors[] = "Email too long";
        }
        
        if (strlen($phone) < 10 || strlen($phone) > 15) {
            $errors[] = "Invalid phone number";
        }
        
        if (empty($input_values['address'])) $errors[] = "Address is required";
        if (strlen($input_values['address']) > 255) $errors[] = "Address too long";
        
        if (empty($input_values['state'])) $errors[] = "State is required";
        if (empty($input_values['sex'])) $errors[] = "Sex is required";
        
        // Password validation (only basic requirements)
        if (empty($_POST['password'])) {
            $errors[] = "Password is required";
        } elseif (strlen($_POST['password']) < 8) {
            $errors[] = "Password must be at least 8 characters";
        } elseif ($_POST['password'] !== $_POST['confirm_password']) {
            $errors[] = "Passwords do not match";
        }
        
        // Check if email exists
        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $errors[] = "Email already registered";
            }
            $stmt->close();
        }
        
        // Handle profile picture upload
        $profile_picture = "default.jpg";
        if (empty($errors) && isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] == 0) {
            $target_dir = __DIR__ . "/uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $file_ext = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
            $allowed_ext = ["jpg", "jpeg", "png"];
            $max_file_size = 2 * 1024 * 1024; // 2MB
            
            if (!in_array($file_ext, $allowed_ext)) {
                $errors[] = "Only JPG, JPEG, PNG files are allowed";
            } elseif ($_FILES["profile_picture"]["size"] > $max_file_size) {
                $errors[] = "File too large (max 2MB)";
            } else {
                $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
                if ($check === false) {
                    $errors[] = "File is not an image";
                } else {
                    $profile_picture = uniqid() . "." . $file_ext;
                    $target_file = $target_dir . $profile_picture;
                    
                    if (!move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                        $errors[] = "Error uploading file";
                    }
                }
            }
        }
        
        // Process registration if no errors
        if (empty($errors)) {
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $otp_code = random_int(100000, 999999);
            $otp_expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $role = "buyer";
            
            // Insert user with prepared statement
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, address, state, sex, password, role, otp_code, otp_expiry, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssssss", 
                $input_values['first_name'],
                $input_values['last_name'],
                $email,
                $phone,
                $input_values['address'],
                $input_values['state'],
                $input_values['sex'],
                $hashed_password,
                $role,
                $otp_code,
                $otp_expiry,
                $profile_picture
            );
            
            if ($stmt->execute()) {
                // Send verification email
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = SMTP_HOST;
                    $mail->SMTPAuth   = true;
                    $mail->Username   = SMTP_USERNAME;
                    $mail->Password   = SMTP_PASSWORD;
                    $mail->SMTPSecure = SMTP_ENCRYPTION;
                    $mail->Port       = SMTP_PORT;
                    $mail->SMTPDebug  = SMTP::DEBUG_OFF;

                    // Recipients
                    $mail->setFrom('no-reply@yourdomain.com', 'F and V Agro Services');
                    $mail->addAddress($email, $input_values['first_name'] . ' ' . $input_values['last_name']);
                    
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Verify Your Account';
                    $verification_url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/verify?email=' . urlencode($email);
                    $mail->Body    = "
                        <h2>Welcome to F and V Agro Services!</h2>
                        <p>Your verification code is: <strong>$otp_code</strong></p>
                        <p>This code will expire in 15 minutes.</p>
                        <p>Click the button below to verify your account:</p>
                        <p><a href=\"$verification_url\" style=\"background-color: #193409; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;\">Verify Account</a></p>
                        <p>If you didn't create an account, please ignore this email.</p>
                    ";
                    $mail->AltBody = "Your verification code is: $otp_code\nThis code will expire in 15 minutes.\nVerify your account at: $verification_url";

                    $mail->send();
                    
                    // Regenerate session ID after successful registration
                    session_regenerate_id(true);
                    
                    // Redirect to verification page
                    $_SESSION['registration_success'] = true;
                    //header("Location: ../verify?email=" . urlencode($email));
                    header("Location: ../login");
                    exit();
                } catch (Exception $e) {
                    error_log("Mailer Error: " . $mail->ErrorInfo);
                    $errors[] = 'Error sending verification email. Please try again after logging in. <a href="../login" class="btn">Login</a>';
                }
            } else {
                $errors[] = "Registration error: " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #27630e;
            --secondary-color: #193409;
            --light-color: #f8f9fa;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .registration-container {
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-title {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .form-title h2 {
            font-weight: 600;
            color: var(--secondary-color);
        }

        .form-title p {
            color: #6c757d;
        }

        .form-group {
            margin-bottom: 1rem;
            position: relative;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 1px solid #ced4da;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(39, 99, 14, 0.25);
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .alert-danger {
            color: #dc3545;
            font-weight: 500;
            text-align: center;
            margin: 1rem 0;
        }

        .registration-footer {
            text-align: center;
            margin-top: 1.5rem;
        }

        .registration-footer a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .registration-footer a:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }

        .input-group {
            position: relative;
        }

        .form-text {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .file-input-label {
            display: block;
            padding: 0.75rem 1rem;
            border: 1px dashed #ced4da;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-input-label:hover {
            border-color: var(--primary-color);
            background-color: rgba(39, 99, 14, 0.05);
        }

        .password-strength {
            height: 5px;
            background: #e9ecef;
            border-radius: 3px;
            margin-top: 5px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s, background-color 0.3s;
        }

        #password-feedback {
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>

<div class="registration-container">
    <div class="form-title">
        <h2><i class="bi bi-person-plus"></i> Buyer Registration</h2>
        <p>Create your account to start shopping</p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <input type="text" name="first_name" class="form-control" placeholder="First Name" 
                           value="<?= htmlspecialchars($input_values['first_name']) ?>" required
                           maxlength="50">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <input type="text" name="last_name" class="form-control" placeholder="Last Name" 
                           value="<?= htmlspecialchars($input_values['last_name']) ?>" required
                           maxlength="50">
                </div>
            </div>
        </div>

        <div class="form-group">
            <input type="email" name="email" class="form-control" placeholder="Email Address" 
                   value="<?= htmlspecialchars($input_values['email']) ?>" required
                   maxlength="100">
        </div>

        <div class="form-group">
            <input type="tel" name="phone" class="form-control" placeholder="Phone Number" 
                   value="<?= htmlspecialchars($input_values['phone']) ?>" required
                   pattern="[0-9]{10,15}" title="10-15 digit phone number">
            <small class="form-text">Format: 10-15 digits (e.g., 08012345678)</small>
        </div>

        <div class="form-group">
            <input type="text" name="address" class="form-control" placeholder="Full Address" 
                   value="<?= htmlspecialchars($input_values['address']) ?>" required
                   maxlength="255">
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <select name="sex" class="form-select" required>
                        <option value="">-- Sex --</option>
                        <option value="Male" <?= $input_values['sex'] === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $input_values['sex'] === 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <select name="state" class="form-select" required>
                        <option value="">-- State --</option>
                        <?php
                        $states = ["Abia", "Adamawa", "Akwa Ibom", "Anambra", "Bauchi", "Bayelsa", "Benue", "Borno", "Cross River", "Delta",
                            "Ebonyi", "Edo", "Ekiti", "Enugu", "Gombe", "Imo", "Jigawa", "Kaduna", "Kano", "Katsina", "Kebbi",
                            "Kogi", "Kwara", "Lagos", "Nasarawa", "Niger", "Ogun", "Ondo", "Osun", "Oyo", "Plateau",
                            "Rivers", "Sokoto", "Taraba", "Yobe", "Zamfara", "FCT Abuja"];
                        foreach ($states as $st) {
                            $selected = $input_values['state'] === $st ? 'selected' : '';
                            echo "<option value=\"$st\" $selected>$st</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="input-group">
                <input type="password" name="password" id="password" class="form-control" 
                       placeholder="Password (min 8 characters)" required
                       minlength="8">
                <span class="password-toggle" onclick="togglePassword('password')">
                    <i class="bi bi-eye"></i>
                </span>
            </div>
            <div class="password-strength">
                <div class="password-strength-bar" id="password-strength-bar"></div>
            </div>
            <div id="password-feedback" class="form-text"></div>
        </div>

        <div class="form-group">
            <div class="input-group">
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" 
                       placeholder="Confirm Password" required>
                <span class="password-toggle" onclick="togglePassword('confirm_password')">
                    <i class="bi bi-eye"></i>
                </span>
            </div>
            <div id="confirm-feedback" class="form-text"></div>
        </div>

        <div class="form-group">
            <label for="profile_picture" class="file-input-label">
                <i class="bi bi-camera"></i> <span id="file-label">Profile Picture (Optional)</span>
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*" class="d-none">
            </label>
            <small class="form-text">JPG, JPEG, or PNG (Max 2MB)</small>
            <div id="image-preview" class="mt-2 text-center"></div>
        </div>

        <div class="form-group form-check mt-3">
            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
            <label class="form-check-label" for="terms">I agree to the <a href="/terms" target="_blank">Terms of Service</a> and <a href="/privacy" target="_blank">Privacy Policy</a></label>
        </div>

        <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary btn-lg w-100" id="submit-btn">
                <i class="bi bi-person-plus"></i> Register Now
            </button>
        </div>
    </form>

    <div class="registration-footer">
        Already have an account? <a href="login"><i class="bi bi-box-arrow-in-right"></i> Login here</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle password visibility
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = field.nextElementSibling.querySelector('i');
        
        if (field.type === "password") {
            field.type = "text";
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            field.type = "password";
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    // Show selected file name and preview
    document.getElementById('profile_picture').addEventListener('change', function(e) {
        const label = document.getElementById('file-label');
        const preview = document.getElementById('image-preview');
        
        if (this.files.length > 0) {
            const file = this.files[0];
            label.textContent = file.name;
            
            // Show image preview
            if (file.type.match('image.*')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-height: 150px;">';
                };
                reader.readAsDataURL(file);
            }
        } else {
            label.textContent = 'Profile Picture (Optional)';
            preview.innerHTML = '';
        }
    });

    // Password strength meter (visual only - no enforcement)
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const strengthBar = document.getElementById('password-strength-bar');
        const feedback = document.getElementById('password-feedback');
        
        // Reset
        strengthBar.style.width = '0%';
        strengthBar.style.backgroundColor = '';
        feedback.textContent = '';
        
        if (password.length === 0) return;
        
        // Calculate strength (for visual feedback only)
        let strength = 0;
        
        // Length
        if (password.length >= 8) strength += 1;
        if (password.length >= 12) strength += 1;
        
        // Complexity
        if (/[A-Z]/.test(password)) strength += 1;
        if (/[a-z]/.test(password)) strength += 1;
        if (/[0-9]/.test(password)) strength += 1;
        if (/[^A-Za-z0-9]/.test(password)) strength += 1;
        
        // Update UI (visual feedback only)
        let width = 0;
        let color = '';
        let message = '';
        
        if (strength <= 2) {
            width = 25;
            color = '#dc3545';
            message = 'Weak';
        } else if (strength <= 4) {
            width = 50;
            color = '#fd7e14';
            message = 'Moderate';
        } else if (strength <= 6) {
            width = 75;
            color = '#ffc107';
            message = 'Strong';
        } else {
            width = 100;
            color = '#28a745';
            message = 'Very Strong';
        }
        
        strengthBar.style.width = width + '%';
        strengthBar.style.backgroundColor = color;
        feedback.textContent = 'Strength: ' + message;
        feedback.style.color = color;
    });

    // Confirm password check
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        const feedback = document.getElementById('confirm-feedback');
        
        if (confirmPassword.length === 0) {
            feedback.textContent = '';
            return;
        }
        
        if (password !== confirmPassword) {
            feedback.textContent = 'Passwords do not match';
            feedback.style.color = '#dc3545';
        } else {
            feedback.textContent = 'Passwords match';
            feedback.style.color = '#28a745';
        }
    });

    // Form submission loading state
    document.querySelector('form').addEventListener('submit', function() {
        const btn = document.getElementById('submit-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Registering...';
    });
</script>
</body>
</html>