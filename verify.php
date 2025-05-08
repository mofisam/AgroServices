<?php
include 'config/db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $otp = trim($_POST["otp"]);

    $stmt = $conn->prepare("SELECT otp_code FROM users WHERE email=? AND is_verified=0");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($stored_otp);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && $otp == $stored_otp) {
        $update_stmt = $conn->prepare("UPDATE users SET is_verified=1, otp_code=NULL WHERE email=?");
        $update_stmt->bind_param("s", $email);
        $update_stmt->execute();
        $message = "<div class='alert alert-success text-center'>✅ Account Verified! <a href='login.php' class='text-success fw-bold'>Login Now</a></div>";
    } else {
        $message = "<div class='alert alert-danger text-center'>❌ Invalid OTP or already verified!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Account - AgriConnect</title>
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

        .verify-container {
            background: #ffffff;
            border-radius: 15px;
            padding: 40px 30px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .verify-header {
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

        .back-link {
            text-align: center;
            margin-top: 15px;
        }

        .back-link a {
            text-decoration: none;
            color: #193409;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="verify-container">
    <div class="verify-header">Verify Your Account</div>
    
    <?php if (!empty($message)) echo $message; ?>

    <form method="post" novalidate>
        <div class="mb-3">
            <label for="email" class="form-label">Registered Email</label>
            <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
        </div>

        <div class="mb-3">
            <label for="otp" class="form-label">OTP Code</label>
            <input type="text" name="otp" class="form-control" placeholder="Enter the 6-digit OTP" required>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Verify Account</button>
        </div>
    </form>

    <div class="back-link mt-3">
        <a href="login.php">Back to Login</a>
    </div>
</div>

</body>
</html>