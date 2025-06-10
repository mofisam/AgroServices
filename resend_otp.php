<?php
// Secure session start
session_start();
session_regenerate_id(true);

// Include configuration files
require __DIR__ . '/config/db.php';
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/.env';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize variables
$message = "";
$email = "";

// CSRF protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token");
    }

    // Sanitize and validate email
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='alert alert-danger text-center'>❌ Invalid email format</div>";
    } else {
        try {
            // Check for unverified user
            $stmt = $conn->prepare("SELECT id, created_at FROM users WHERE email=? AND is_verified=0");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // Generate OTP with cryptographic randomness
                $otp_code = random_int(100000, 999999);
                
                // Set OTP expiration (10 minutes from now)
                $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                
                // Update user record with new OTP
                $update_stmt = $conn->prepare("UPDATE users SET otp_code=?, otp_expiry=? WHERE email=?");
                $update_stmt->bind_param("sss", $otp_code, $otp_expiry, $email);
                $update_stmt->execute();

                // Send OTP email
                $mail = new PHPMailer(true); // Enable exceptions
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = SMTP_HOST;
                    $mail->SMTPAuth   = true;
                    $mail->Username   = SMTP_USERNAME;
                    $mail->Password   = SMTP_PASSWORD;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = SMTP_PORT;
                    $mail->SMTPDebug  = 0; // Set to 2 for debugging

                    // Recipients
                    $mail->setFrom('no-reply@fandvagroservices.com.ng', 'F and V Agroservices');
                    $mail->addAddress($email);
                    
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Your New OTP Code';
                    $mail->Body    = sprintf(
                        '<h2>Your Verification Code</h2>
                        <p>Your new OTP code is: <strong>%s</strong></p>
                        <p>This code will expire in 10 minutes.</p>
                        <p><a href="%s">Click here to verify</a></p>',
                        $otp_code,
                        'https://' . $_SERVER['HTTP_HOST'] . '/web/AgroServices/verify?email=' . urlencode($email)
                    );
                    $mail->AltBody = sprintf(
                        "Your new OTP code is: %s\nThis code will expire in 10 minutes.\nVerify at: %s",
                        $otp_code,
                        'https://' . $_SERVER['HTTP_HOST'] . '/web/AgroServices/verify?email=' . urlencode($email)
                    );

                    $mail->send();
                    $message = "<div class='alert alert-success text-center'>✅ OTP resent successfully! Check your email.</div>";
                    
                    // Log successful OTP resend
                    error_log("OTP resent for email: $email");
                } catch (Exception $e) {
                    error_log("Mailer Error: " . $mail->ErrorInfo);
                    $message = "<div class='alert alert-danger text-center'>❌ Failed to send OTP. Please try again later.</div>";
                }
            } else {
                $message = "<div class='alert alert-warning text-center'>❌ No unverified account found with this email.</div>";
            }
        } catch (Exception $e) {
            error_log("Database Error: " . $e->getMessage());
            $message = "<div class='alert alert-danger text-center'>❌ System error. Please try again later.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resend OTP - F and V Agroservices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #27630e;
            --secondary-color: #193409;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .resend-container {
            background: #fff;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .resend-title {
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

        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-link a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .back-link a:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }

        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="resend-container">
    <div class="resend-title">Resend OTP Code</div>

    <?= $message ?>

    <form method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        
        <div class="mb-3">
            <label for="email" class="form-label">Registered Email</label>
            <input type="email" name="email" class="form-control" 
                   placeholder="Enter your registered email" 
                   value="<?= htmlspecialchars($email) ?>" 
                   required
                   autocomplete="email">
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">Send New OTP</button>
        </div>
    </form>

    <div class="back-link">
        <a href="login"><i class="bi bi-arrow-left"></i> Back to Login</a>
    </div>
</div>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>