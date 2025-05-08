<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/../config/db.php';

// 🛒 Cart Count Logic
$cart_count = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'] ?? 1;
    }
}

// ✉️ Message Count Logic
$msg_count = 0;
if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    $query = '';
    if ($role === 'buyer') {
        $query = "SELECT COUNT(*) FROM product_inquiries WHERE user_id = ? AND sender_role = 'seller' AND read_status = 'unread'";
    } elseif ($role === 'seller') {
        $query = "SELECT COUNT(*) FROM product_inquiries WHERE seller_id = ? AND sender_role = 'buyer' AND read_status = 'unread'";
    }

    if ($query && isset($conn)) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($msg_count);
        $stmt->fetch();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>F and V Agro Services</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Custom Styles -->
  <link rel="stylesheet" href="http://localhost/web/agroservices/includes/style.css">

  <!-- Inline Beautified Header Styles -->
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f9f9f9;
    }

    .navbar {
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      background-color: #14532d; /* Dark green shade */
    }

    .navbar-brand img {
      max-height: 45px;
      border-radius: 4px;
    }

    .nav-link {
      color: #ffffff !important;
      font-weight: 500;
      padding: 0.5rem 1rem;
      position: relative;
    }

    .nav-link:hover {
      color: #ffd700 !important;
      background-color: rgba(255, 255, 255, 0.1);
      border-radius: 5px;
      transition: 0.3s;
    }

    .custom-toggler {
      border: none;
      background-color: rgba(255, 255, 255, 0.2);
      border-radius: 5px;
    }

    .custom-toggler:hover {
      background-color: rgba(255, 255, 255, 0.3);
    }

    .cart-badge {
      font-size: 0.7rem;
      background: red;
      color: white;
      padding: 2px 6px;
      border-radius: 50%;
      position: absolute;
      top: 4px;
      right: 0;
      transform: translate(50%, -50%);
      line-height: 1;
      min-width: 18px;
      text-align: center;
    }

    .nav-icon {
      font-size: 1.2rem;
      color: #fff;
    }

    .nav-icon:hover {
      color: #ffd700;
    }

    .nav-item {
      margin-left: 10px;
    }

    @media (max-width: 991.98px) {
      .nav-item {
        margin-left: 0;
        margin-bottom: 5px;
      }
    }
  </style>
</head>

<body>
<!-- Beautiful Navbar -->
<nav class="navbar navbar-expand-lg">
  <div class="container">

    <!-- Logo -->
    <a class="navbar-brand d-flex align-items-center" href="http://localhost/web/agroservices/index.php">
      <img src="http://localhost/web/agroservices/assets/images/logo.jpg" alt="F and V Agro Services" onerror="this.style.display='none';">
    </a>

    <!-- Mobile Menu Toggle -->
    <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Links -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="http://localhost/web/agroservices/products/index.php">🛍️ Marketplace</a></li>
        <li class="nav-item"><a class="nav-link" href="http://localhost/web/agroservices/services.php">💼 Services</a></li>
        <li class="nav-item"><a class="nav-link" href="http://localhost/web/agroservices/about us.php">📖 About</a></li>
        <li class="nav-item"><a class="nav-link" href="http://localhost/web/agroservices/contact.php">📞 Contact</a></li>
      </ul>

      <ul class="navbar-nav mb-2 mb-lg-0">
        <!--  User Links -->
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-person-circle nav-icon"></i> Dashboard</a></li>
          <li class="nav-item"><a class="nav-link text-warning" href="/logout.php"><i class="bi bi-box-arrow-right nav-icon"></i> Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right nav-icon"></i> Login</a></li>
          <li class="nav-item"><a class="nav-link" href="register.php"><i class="bi bi-person-plus-fill nav-icon"></i> Register</a></li>
        <?php endif; ?>

        <!-- ✉️ Messages -->
        <li class="nav-item position-relative me-3">
          <a href="<?= ($_SESSION['role'] ?? '') === 'buyer' ? 'http://localhost/web/agroservices/messages/' : '/products/seller_messages.php' ?>" class="nav-link">
            <i class="bi bi-envelope-fill nav-icon"></i>
            <?php if ($msg_count > 0): ?>
              <span class="cart-badge"><?= $msg_count ?></span>
            <?php endif; ?>
          </a>
        </li>

        <!-- 🛒 Cart -->
        <li class="nav-item position-relative">
          <a href="http://localhost/web/agroservices/cart" class="nav-link">
            <i class="bi bi-cart-fill nav-icon"></i>
            <?php if ($cart_count > 0): ?>
              <span class="cart-badge"><?= $cart_count ?></span>
            <?php endif; ?>
          </a>
        </li>

      </ul>
    </div>
  </div>
</nav>