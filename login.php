<?php
declare(strict_types=1);

// Secure session configuration
session_start([
    'cookie_lifetime' => 86400,
    'cookie_secure'   => true,
    'cookie_httponly' => true,
    'use_strict_mode' => true
]);

require 'config/db.php';

// Redirect if already logged in
if (isset($_SESSION["user_id"])) {
    redirectBasedOnRole($_SESSION["role"]);
    exit();
}

// Handle login attempts
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Security error: Invalid request");
    }

    // Rate limiting
    if ($_SESSION['login_attempts'] >= 5) {
        $error = "Too many attempts. Try again in 30 minutes.";
    } else {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST["password"] ?? '';

        // Database query (always executed to prevent timing attacks)
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, role, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        $loginSuccessful = false;
        $userExists = $stmt->num_rows > 0;

        if ($userExists) {
            $stmt->bind_result($id, $first_name, $last_name, $email, $hashed_password, $role, $status);
            $stmt->fetch();

            if ($status != "suspended" && password_verify($password, $hashed_password)) {
                $loginSuccessful = true;
                
                // Update last login
                $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $id);
                $update_stmt->execute();

                // Set session
                $_SESSION["user_id"] = $id;
                $_SESSION["first_name"] = $first_name;
                $_SESSION["last_name"] = $last_name;
                $_SESSION["email"] = $email;
                $_SESSION["role"] = $role;

                // Handle remember me securely
                if (isset($_POST["remember_me"])) {
                    $token = bin2hex(random_bytes(32));
                    $hashedToken = password_hash($token, PASSWORD_DEFAULT);
                    
                    $stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                    $stmt->bind_param("si", $hashedToken, $id);
                    $stmt->execute();
                    
                    setcookie(
                        "remember_token",
                        $token,
                        [
                            'expires' => time() + 86400 * 30,
                            'path' => '/',
                            'secure' => true,
                            'httponly' => true,
                            'samesite' => 'Strict'
                        ]
                    );
                }

                redirectBasedOnRole($role);
                exit();
            }
        }

        if (!$loginSuccessful) {
            $_SESSION['login_attempts']++;
            $error = "❌ Incorrect email or password!";
        }
    }
}

// Generate CSRF token
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

function redirectBasedOnRole(string $role): void {
    $locations = [
        'buyer' => 'dashboard',
        'seller' => 'seller/dashboard',
        'admin' => 'admin/admin_dashboard'
    ];
    header("Location: " . ($locations[$role] ?? '/'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - F and V Agro Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg,rgb(39, 83, 14), #193409);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: #ffffff;
            border-radius: 15px;
            padding: 40px 30px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .login-logo img {
            width: 80px;
        }
        .form-group-icon {
            position: relative;
        }
        .form-group-icon i {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #6c757d;
        }
        .form-group-icon .eye{
            left: 90%;
        }
        .form-group-icon input {
            padding-left: 40px;
        }
        .show-password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        .form-check-label {
            font-size: 0.9rem;
        }
        .form-check {
            display: flex;
            align-items: center;
        }
        .forgot-password {
            font-size: 0.9rem;
            text-decoration: none;
            color: #4a90e2;
        }
        .forgot-password:hover {
            text-decoration: underline;
        }
        .btn-primary {
            background-color: #193409;
            border: none;
        }
        .btn-primary:hover {
            background-color:rgb(73, 128, 40);
        }
        .error-message {
            color: #dc3545;
            font-weight: 500;
            text-align: center;
            margin-top: 15px;
        }
        .text-register {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <img src="assets/images/logo.jpg" alt="F and V Agroservices Logo">
            <h4 class="mt-2">Welcome to F and V Agro Services</h4>
        </div>

        <form method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            
            <div class="form-group-icon mb-3">
                <i class="bi bi-envelope-fill"></i>
                <input type="email" class="form-control" name="email" placeholder="Email address" required 
                       value="<?= isset($_COOKIE['user_email']) ? htmlspecialchars($_COOKIE['user_email'], ENT_QUOTES) : '' ?>">
            </div>

            <div class="form-group-icon mb-3">
                <i class="bi bi-lock-fill"></i>
                <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                <i class="bi bi-eye-fill show-password-toggle eye" id="togglePassword" onclick="togglePasswordVisibility()"></i>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="rememberMe" name="remember_me">
                    <label class="form-check-label" for="rememberMe">Remember Me</label>
                </div>
                <a href="forgot_password" class="forgot-password">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <?php if (isset($error)) { ?>
            <div class="error-message">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php } ?>

        <div class="text-center mt-3 text-register">
            <span>Don't have an account?</span>
            <a href="registration" class="text-primary fw-semibold">Register here</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePasswordVisibility() {
            const password = document.getElementById("password");
            const toggleIcon = document.getElementById("togglePassword");
            if (password.type === "password") {
                password.type = "text";
                toggleIcon.classList.replace("bi-eye-fill", "bi-eye-slash-fill");
            } else {
                password.type = "password";
                toggleIcon.classList.replace("bi-eye-slash-fill", "bi-eye-fill");
            }
        }
    </script>
</body>
</html>