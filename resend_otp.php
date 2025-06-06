<?php
include 'config/db.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
use PHPMailer\PHPMailer\PHPMailer;

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email=? AND is_verified=0");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $otp_code = rand(100000, 999999);
        
        $update_stmt = $conn->prepare("UPDATE users SET otp_code=? WHERE email=?");
        $update_stmt->bind_param("ss", $otp_code, $email);
        $update_stmt->execute();

        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@example.com';
        $mail->Password = 'your_email_password';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('your_email@example.com', 'Agro E-commerce');
        $mail->addAddress($email);
        $mail->Subject = "Resend OTP - Agro E-commerce";
        $mail->isHTML(true);
        $mail->Body = "Your new OTP is: <strong>$otp_code</strong>. Click to verify: <a href='verify?email=$email'>Verify Now</a>";

        if ($mail->send()) {
            $message = "<div class='alert alert-success text-center'>✅ OTP Resent! Check your email.</div>";
        } else {
            $message = "<div class='alert alert-danger text-center'>❌ Failed to send OTP: " . $mail->ErrorInfo . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning text-center'>❌ No unverified account found for this email.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resend OTP - AgriConnect</title>
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

        .resend-container {
            background: #fff;
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
        }

        .resend-title {
            font-size: 24px;
            font-weight: 600;
            color: #193409;
            text-align: center;
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 500;
        }

        .form-control {
            padding: 12px;
            border-radius: 10px;
        }

        .btn-primary {
            background-color: #193409;
            border: none;
            font-weight: bold;
        }

        .btn-primary:hover {
            background-color: rgb(73, 128, 40);
        }

        .back-link {
            text-align: center;
            margin-top: 15px;
        }

        .back-link a {
            color: #193409;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="resend-container">
    <div class="resend-title">Resend OTP</div>

    <?= $message ?>

    <form method="post" novalidate>
        <div class="mb-3">
            <label for="email" class="form-label">Registered Email</label>
            <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Send New OTP</button>
        </div>
    </form>

    <div class="back-link mt-3">
        <a href="login">Back to Login</a>
    </div>
</div>

</body>
</html>
