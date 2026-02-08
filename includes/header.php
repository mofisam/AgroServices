<?php


if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/../config/db.php';

// 🛒 Cart Count Logic (moved to separate endpoint)
$cart_count = 0;
if (!empty($_SESSION['cart'])) {
    $cart_count = count($_SESSION['cart']);
}

// ✉️ Message Count Logic (unchanged)
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
  <!-- Primary Meta Tags -->
  <title><?= $page_title ?? 'F and V Agro Services | Nigeria\'s Trusted Agro-Commerce Platform' ?></title>
  <meta name="description" content="<?= $page_description ?? 'Nigeria\'s leading digital agro-commerce platform connecting farmers and buyers for safe, transparent trade.' ?>">
  <meta name="keywords" content="<?= $page_keywords ?? 'agro-commerce Nigeria, digital farming platform, agricultural trade' ?>">
  <meta name="author" content="F and V Agro Services">
  <meta name="robots" content="index, follow">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= $page_title ?? 'F and V Agro Services | Digitizing Agricultural Trade' ?>">
  <meta property="og:description" content="<?= $page_description ?? 'Empowering farmers and buyers through Nigeria\'s trusted agro-commerce platform.' ?>">
  <meta property="og:image" content="<?= $og_image ?? 'https://www.fandvagroservices.com.ng/assets/images/logo.jpg' ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:url" content="<?= $current_url ?? 'https://www.fandvagroservices.com.ng/' ?>">
  <meta property="og:site_name" content="F and V Agro Services">

  <!-- X/Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= $page_title ?? 'F and V Agro Services | Agro-Commerce Platform' ?>">
  <meta name="twitter:description" content="<?= $page_description ?? 'Real-time agricultural trade platform for Nigerian farmers and buyers.' ?>">
  <meta name="twitter:image" content="<?= $og_image ?? 'https://www.fandvagroservices.com.ng/assets/images/logo.jpg' ?>">
  <meta name="twitter:site" content="@FandVAgro">

  <!-- Canonical URL -->
  <link rel="canonical" href="<?= $current_url ?? 'https://www.fandvagroservices.com.ng/' ?>">

  <!-- Mobile/Favicon -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="/favicon.ico">

  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
  <link rel="shortcut icon" href="/favicon.ico" />
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
  <link rel="manifest" href="/site.webmanifest" />

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Custom Styles -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/includes/style.css">

  <!-- Inline Styles with Cart Auto-Update -->
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
    
    .green-bg {
      background-color:rgb(25, 96, 53)
    }

    @media (max-width: 991.98px) {
      .nav-item {
        margin-left: 0;
        margin-bottom: 5px;
      }
    }
  </style>

  <!-- Cart Auto-Update Script -->
  <script>
  $(document).ready(function() {
      // Function to update cart count
      function updateCartCount() {
          $.ajax({
              url: '<?= BASE_URL ?>/includes/get_cart_count.php',
              method: 'GET',
              success: function(response) {
                  const cartCount = parseInt(response.count) || 0;
                  const $badge = $('#cart-badge');
                  
                  if (cartCount > 0) {
                      $badge.text(cartCount).show();
                  } else {
                      $badge.hide();
                  }
              },
              error: function(xhr, status, error) {
                  console.error('Error fetching cart count:', error);
              }
          });
      }

      // Update immediately on page load
      updateCartCount();

      // Set up periodic updates (every 5 seconds)
      setInterval(updateCartCount, 5000);

      // Also update when visibility changes (if user switches tabs)
      $(window).on('focus', updateCartCount);
  });
  </script>
</head>

<body>
<!-- Beautiful Navbar -->
<nav class="navbar navbar-expand-lg">
  <div class="container">

    <!-- Logo -->
    <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>/">
      <img src="<?= BASE_URL ?>/assets/images/logo.jpg" alt="F and V Agro Services" onerror="this.style.display='none';">
      <span class="nav-link">Home</SPAN>
    </a>

    <!-- Mobile Menu Toggle -->
    <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Links -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/products">🛍️ Marketplace</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/services">💼 Services</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/about us">📖 About</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/contact">📞 Contact</a></li>
      </ul>

      <ul class="navbar-nav mb-2 mb-lg-0">
        <!-- 👤 User Session Check -->
        <?php if (isset($_SESSION['user_id'])): 
          $role = $_SESSION['role'] ?? '';
         

          // Dashboard Links Based on Role
          switch ($role) {
            case 'buyer':
              $dashboard_url = BASE_URL . "/dashboard";
              $message_url   = BASE_URL . "/messages";
              break;
            case 'seller':
              $dashboard_url = BASE_URL . "/seller";
              $message_url   = BASE_URL . "/seller/messages/index";
              break;
            case 'admin':
              $dashboard_url = BASE_URL . "/admin/admin_dashboard.php";
              $message_url   = BASE_URL . "/admin/messages";
              break;
            default:
              $dashboard_url = BASE_URL . "/dashboard";
              $message_url   = BASE_URL . "/messages";
              break;
          }
        ?>
          <!-- 🧭 Dashboard -->
          <li class="nav-item">
            <a class="nav-link" href="<?= $dashboard_url ?>">
              <i class="bi bi-person-circle nav-icon"></i> Dashboard
            </a>
          </li>

          <!-- 🚪 Logout -->
          <li class="nav-item">
            <a class="nav-link text-warning" href="<?= BASE_URL ?>/logout">
              <i class="bi bi-box-arrow-right nav-icon"></i> Logout
            </a>
          </li>

          <!-- ✉️ Messages (Role-Based) -->
          <li class="nav-item position-relative me-3">
            <a href="<?= $message_url ?>" class="nav-link">
              <i class="bi bi-envelope-fill nav-icon"></i>
              <?php if (!empty($msg_count) && $msg_count > 0): ?>
                <span class="cart-badge"><?= $msg_count ?></span>
              <?php endif; ?>
            </a>
          </li>
        <?php else: ?>
          <!-- 🔐 Login / Register -->
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/login">
              <i class="bi bi-box-arrow-in-right nav-icon"></i> Login
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>/registration/index">
              <i class="bi bi-person-plus-fill nav-icon"></i> Register
            </a>
          </li>
        <?php endif; ?>

        <!-- 🛒 Cart -->
        <li class="nav-item position-relative">
          <a href="<?= BASE_URL ?>/cart" class="nav-link">
            <i class="bi bi-cart-fill nav-icon"></i>
            <span id="cart-badge" class="cart-badge" <?= isset($cart_count) && $cart_count > 0 ? '' : 'style="display: none;"' ?>>
              <?= isset($cart_count) && $cart_count > 0 ? $cart_count : '' ?>
            </span>
          </a>
        </li>
      </ul>

    </div>
  </div>
</nav>