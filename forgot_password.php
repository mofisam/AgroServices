<?php
include 'config/db.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
use PHPMailer\PHPMailer\PHPMailer;

require 'vendor/autoload.php';
require_once 'config/.env.php'; // Load environment variables

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $reset_token = bin2hex(random_bytes(32));
        $expiry_time = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update_stmt = $conn->prepare("UPDATE users SET reset_token=?, reset_token_expiry=? WHERE email=?");
        $update_stmt->bind_param("sss", $reset_token, $expiry_time, $email);
        $update_stmt->execute();

        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->Port = SMTP_PORT;
        
        $mail->setFrom('no-reply@fandvagroservices.com.ng', 'F and V Agroservices');
        $mail->addAddress($email);
        $mail->Subject = "Password Reset Request";
        $mail->isHTML(true);
        $mail->Body = "Click here to reset your password: <a href='https://fandvagroservices.com.ng/reset_password?token=$reset_token'>Reset Password</a>";

        if ($mail->send()) {
            $message = "<div class='alert alert-success text-center'>✅ Password reset email sent!</div>";
        } else {
            $message = "<div class='alert alert-danger text-center'>❌ Failed to send email: " . $mail->ErrorInfo . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning text-center'>❌ Email not found!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - AgriConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, rgb(39, 83, 14), #193409);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .reset-container {
            background: #ffffff;
            border-radius: 15px;
            padding: 40px 30px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .reset-header {
            font-weight: 600;
            font-size: 24px;
            text-align: center;
            color: #193409;
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 500;
        }

        .form-control {
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #ccc;
        }

        .btn-primary {
            background-color: #193409;
            border: none;
            font-weight: bold;
        }

        .btn-primary:hover {
            background-color: rgb(73, 128, 40);
        }

        .card-footer {
            text-align: center;
            margin-top: 20px;
        }

        .card-footer a {
            color: #193409;
            text-decoration: none;
            font-weight: 500;
        }

        .card-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="reset-container">
    <div class="reset-header">Reset Your Password</div>
    
    <?php if (!empty($message)) echo $message; ?>

    <form method="post" novalidate>
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="Enter your registered email" required>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Send Reset Link</button>
        </div>
    </form>

    <div class="card-footer">
        <a href="login">Back to Login</a>
    </div>
</div>

</body>
</html>
