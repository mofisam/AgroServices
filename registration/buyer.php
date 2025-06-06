<?php
session_start();
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once '../config/.env.php'; // Load environment variables

include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);
    $state = trim($_POST["state"]);
    $sex = trim($_POST["sex"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $role = "buyer";
    $otp_code = rand(100000, 999999);
    $profile_picture = "default.jpg";

    if ($password !== $confirm_password) {
        $error = "❌ Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        if (isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] == 0) {
            $target_dir = "uploads/";
            $file_ext = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));

            if (in_array($file_ext, ["jpg", "jpeg", "png"])) {
                $profile_picture = uniqid() . "." . $file_ext;
                move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_dir . $profile_picture);
            } else {
                $error = "❌ Only JPG, JPEG, and PNG files are allowed!";
            }
        }

        if (!isset($error)) {
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, address, state, sex, password, role, otp_code, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssssss", $first_name, $last_name, $email, $phone, $address, $state, $sex, $hashed_password, $role, $otp_code, $profile_picture);

            if ($stmt->execute()) {
                $mail = new PHPMailer();
                $mail->isSMTP();
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USERNAME;
                $mail->Password = SMTP_PASSWORD;
                $mail->SMTPSecure = 'tls';
                $mail->Port = SMTP_PORT;
                $mail->setFrom('malaosamuel2020@gmail.com', 'Agro E-commerce');
                $mail->addAddress($email, "$first_name $last_name");
                $mail->Subject = "Verify Your Account";
                $mail->Body = "Your OTP is: $otp_code. Click here to verify: <a href='verify?email=$email'>Verify Now</a>";
                $mail->isHTML(true);

                if ($mail->send()) {
                    header("Location: verify?email=$email");
                    exit();
                } else {
                    $error = "❌ Email sending failed: " . $mail->ErrorInfo;
                }
            } else {
                $error = "❌ Registration error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - AgriConnect</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg,rgb(39, 83, 14), #193409);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .registration-container {
            background: #ffffff;
            padding: 40px 30px;
            border-radius: 15px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .form-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-title h2 {
            font-weight: 600;
            color: #193409;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group input, .form-group select {
            padding: 12px;
            font-size: 15px;
            border-radius: 10px;
            border: 1px solid #ccc;
            width: 100%;
        }

        .btn-primary {
            background-color: #193409;
            border: none;
            font-weight: bold;
        }

        .btn-primary:hover {
            background-color: rgb(73, 128, 40);
        }

        .error-message {
            color: #dc3545;
            font-weight: 500;
            text-align: center;
            margin-top: 10px;
        }

        .registration-footer {
            text-align: center;
            margin-top: 15px;
        }

        .registration-footer a {
            color: #193409;
            text-decoration: none;
            font-weight: 500;
        }

        .registration-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<br/><br/>
<div class="registration-container">
    <div class="form-title">
        <h2>Buyer Registration</h2>
        <p>Join F and V Agro Services to enhance your agricultural experience.</p>
    </div>

    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <input type="text" name="first_name" placeholder="First Name" required>
        </div>
        <div class="form-group">
            <input type="text" name="last_name" placeholder="Last Name" required>
        </div>
        <div class="form-group">
            <input type="email" name="email" placeholder="Email" required>
        </div>
        <div class="form-group">
            <input type="tel" name="phone" placeholder="Phone Number" required>
        </div>
        <div class="form-group">
            <input type="text" name="address" placeholder="Full Address" required>
        </div>
        <div class="form-group">
            <select name="sex" required>
                <option value="">-- Select Gender --</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
        </div>
        <div class="form-group">
            <select name="state" required>
                <option value="">-- Select State --</option>
                <?php
                $states = ["Abia", "Adamawa", "Akwa Ibom", "Anambra", "Bauchi", "Bayelsa", "Benue", "Borno", "Cross River", "Delta",
                    "Ebonyi", "Edo", "Ekiti", "Enugu", "Gombe", "Imo", "Jigawa", "Kaduna", "Kano", "Katsina", "Kebbi",
                    "Kogi", "Kwara", "Lagos", "Nasarawa", "Niger", "Ogun", "Ondo", "Osun", "Oyo", "Plateau",
                    "Rivers", "Sokoto", "Taraba", "Yobe", "Zamfara", "FCT Abuja"];
                foreach ($states as $st) {
                    echo "<option value=\"$st\">$st</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <div class="form-group">
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        </div>
        <div class="form-group">
            <input type="file" name="profile_picture" accept="image/*">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary w-100">Register</button>
        </div>

        <?php if (isset($error)) { ?>
            <div class="error-message">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php } ?>
    </form>

    <div class="registration-footer">
        Already have an account? <a href="login">Login here</a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.
