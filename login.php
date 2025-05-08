<?php 
session_start();
include 'config/db.php';

// Check if the user is already logged in
if (isset($_SESSION["user_id"])) {
    if ($_SESSION["role"] === "buyer") {
        header("Location: dashboard.php");
    } elseif ($_SESSION["role"] === "seller") {
        header("Location: seller_dashboard.php");
    } else {
        header("Location: admin/admin_dashboard.php");
    }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, role, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $first_name, $last_name, $email, $hashed_password, $role, $status);
        $stmt->fetch();

        if ($status == "suspended") {
            die("Your account is suspended. Contact admin.");
        }

        if (password_verify($password, $hashed_password)) {
            $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $update_stmt->bind_param("i", $id);
            $update_stmt->execute();

            $_SESSION["user_id"] = $id;
            $_SESSION["first_name"] = $first_name;
            $_SESSION["last_name"] = $last_name;
            $_SESSION["email"] = $email;
            $_SESSION["role"] = $role;

            // Remember Me functionality
            if (isset($_POST["remember_me"])) {
                setcookie("user_email", $email, time() + (86400 * 30), "/"); // 30 days cookie
                setcookie("user_password", $password, time() + (86400 * 30), "/");
            } else {
                setcookie("user_email", "", time() - 3600, "/");
                setcookie("user_password", "", time() - 3600, "/");
            }

            if ($role === "buyer") {
                header("Location: buyer_dashboard.php");
            } elseif ($role === "seller") {
                header("Location: seller_dashboard.php");
            } else {
                header("Location: admin/admin_dashboard.php");
            }
            exit();
        } else {
            $error = "❌ Incorrect email or password!";
        }
    } else {
        $error = "❌ Account not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - AgriConnect</title>
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
        <img src="assets/images/logo.jpg" alt="AgriConnect Logo">
        <h4 class="mt-2">Welcome to F and V Agro Services</h4>
    </div>

    <form method="post" novalidate>
        <div class="form-group-icon mb-3">
            <i class="bi bi-envelope-fill"></i>
            <input type="email" class="form-control" name="email" placeholder="Email address" required value="<?php echo isset($_COOKIE['user_email']) ? $_COOKIE['user_email'] : ''; ?>">
        </div>

        <div class="form-group-icon mb-3">
            <i class="bi bi-lock-fill"></i>
            <input type="password" class="form-control" name="password" id="password" placeholder="Password" required value="<?php echo isset($_COOKIE['user_password']) ? $_COOKIE['user_password'] : ''; ?>">
            <i class="bi bi-eye-fill show-password-toggle eye" id="togglePassword" onclick="togglePasswordVisibility()"></i>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="rememberMe" name="remember_me" <?php echo isset($_COOKIE['user_email']) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="rememberMe">Remember Me</label>
            </div>
            <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <?php if (isset($error)) { ?>
        <div class="error-message">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php } ?>

    <!-- Registration link -->
    <div class="text-center mt-3 text-register">
        <span>Don't have an account?</span>
        <a href="register.php" class="text-primary fw-semibold">Register here</a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Password Toggle Script -->
<script>
    function togglePasswordVisibility() {
        const password = document.getElementById("password");
        const toggleIcon = document.getElementById("togglePassword");

        if (password.type === "password") {
            password.type = "text";
            toggleIcon.classList.remove("bi-eye-fill");
            toggleIcon.classList.add("bi-eye-slash-fill");
        } else {
            password.type = "password";
            toggleIcon.classList.remove("bi-eye-slash-fill");
            toggleIcon.classList.add("bi-eye-fill");
        }
    }
</script>

</body>
</html>
