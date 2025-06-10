<?php
// Secure session start
session_start();
session_regenerate_id(true);

// Include configuration
require __DIR__ . '/config/db.php';

// Initialize variables
$message = '';
$token = $_GET['token'] ?? '';
$password_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = trim($_POST["token"]);
    $new_password = trim($_POST["new_password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    // Validate password
    if (empty($new_password)) {
        $password_error = "Password is required";
    } elseif (strlen($new_password) < 6) {
        $password_error = "Password must be at least 8 characters";
    } elseif (!preg_match("/[A-Z]/", $new_password)) {
        $password_error = "Password must contain at least one uppercase letter";
    } elseif (!preg_match("/[a-z]/", $new_password)) {
        $password_error = "Password must contain at least one lowercase letter";
    } elseif (!preg_match("/[0-9]/", $new_password)) {
        $password_error = "Password must contain at least one number";
    } elseif ($new_password !== $confirm_password) {
        $password_error = "Passwords do not match";
    }

    if (empty($password_error)) {
        try {
            // Check if token is valid and not expired
            $stmt = $conn->prepare("SELECT email FROM users WHERE reset_token=? AND reset_token_expiry > NOW()");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($email);
            $stmt->fetch();

            if ($stmt->num_rows > 0) {
                // Hash the new password
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password and clear token
                $update_stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, reset_token_expiry=NULL, password_changed_at=NOW() WHERE email=?");
                $update_stmt->bind_param("ss", $password_hash, $email);
                
                if ($update_stmt->execute()) {
                    // Log the password change
                    error_log("Password reset for email: $email");
                    
                    // Send email notification
                    require __DIR__ . '/vendor/autoload.php';
                    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = SMTP_HOST;
                        $mail->SMTPAuth = true;
                        $mail->Username = SMTP_USERNAME;
                        $mail->Password = SMTP_PASSWORD;
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = SMTP_PORT;

                        $mail->setFrom('no-reply@fandvagroservices.com.ng', 'F and V Agroservices');
                        $mail->addAddress($email);
                        $mail->Subject = 'Your Password Has Been Reset';
                        $mail->isHTML(true);
                        $mail->Body = "
                            <h2>Password Reset Confirmation</h2>
                            <p>Your password was successfully changed on " . date('F j, Y \a\t g:i a') . ".</p>
                            <p>If you didn't request this change, please contact our support team immediately.</p>
                            <p><a href='" . (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . "/login'>Login to your account</a></p>
                        ";
                        $mail->send();
                    } catch (Exception $e) {
                        error_log("Password reset email failed: " . $mail->ErrorInfo);
                    }

                    $message = "<div class='alert alert-success'>✅ Password reset successful! <a href='login' class='alert-link'>Login Now</a></div>";
                } else {
                    throw new Exception("Database update failed");
                }
            } else {
                $message = "<div class='alert alert-danger'>❌ Invalid or expired token. Please request a new password reset link.</div>";
            }
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            $message = "<div class='alert alert-danger'>❌ System error. Please try again later.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>❌ $password_error</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | F and V Agroservices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #27630e;
            --secondary-color: #193409;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, rgb(39, 83, 14), #193409);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .reset-container {
            background: #fff;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 450px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .reset-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--secondary-color);
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 1px solid #ced4da;
            transition: border-color 0.3s;
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
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .password-strength {
            margin-top: 5px;
            font-size: 0.85rem;
        }

        .password-rules {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 5px;
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
    </style>
</head>
<body>

<div class="reset-container">
    <div class="reset-title">
        <i class="bi bi-shield-lock"></i> Reset Password
    </div>

    <?= $message ?>

    <form method="post" novalidate>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <div class="mb-3">
            <label for="new_password" class="form-label">New Password</label>
            <div class="input-group">
                <input type="password" name="new_password" id="new_password" 
                       class="form-control" placeholder="Enter new password" required
                       pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}"
                       title="Must contain at least 6 characters, one uppercase, one lowercase and one number">
                <span class="password-toggle" onclick="togglePassword('new_password')">
                    <i class="bi bi-eye"></i>
                </span>
            </div>
            <div class="password-rules">
                <small>Must contain: 8+ characters, uppercase, lowercase, number, and special character</small>
            </div>
        </div>

        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <div class="input-group">
                <input type="password" name="confirm_password" id="confirm_password" 
                       class="form-control" placeholder="Confirm new password" required>
                <span class="password-toggle" onclick="togglePassword('confirm_password')">
                    <i class="bi bi-eye"></i>
                </span>
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-arrow-repeat"></i> Reset Password
            </button>
        </div>
    </form>

    <div class="text-center mt-3">
        <a href="login" class="text-decoration-none">
            <i class="bi bi-arrow-left"></i> Back to Login
        </a>
    </div>
</div>

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

    // Password strength indicator (optional)
    document.getElementById('new_password').addEventListener('input', function() {
        // Implement password strength meter if desired
    });
</script>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>