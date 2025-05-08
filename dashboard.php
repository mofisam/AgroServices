<?php
session_start();
include 'config/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit();
}

$buyer_id = $_SESSION['user_id'];

// 🧾 Fetch user profile
$profile_stmt = $conn->prepare("SELECT first_name, last_name, email, state, profile_picture FROM users WHERE id = ?");
$profile_stmt->bind_param("i", $buyer_id);
$profile_stmt->execute();
$profile = $profile_stmt->get_result()->fetch_assoc();
$profile_stmt->close();

// 🧠 Count Helper
function getCount($conn, $query, $param) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $param);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count;
}

// 📊 Stats
$total_orders = getCount($conn, "SELECT COUNT(*) FROM orders WHERE buyer_id = ?", $buyer_id);
$delivered_orders = getCount($conn, "SELECT COUNT(*) FROM orders WHERE buyer_id = ? AND delivery_status = 'delivered'", $buyer_id);
$pending_orders = getCount($conn, "SELECT COUNT(*) FROM orders WHERE buyer_id = ? AND delivery_status != 'delivered'", $buyer_id);
$total_wishlist = getCount($conn, "SELECT COUNT(*) FROM wishlists WHERE user_id = ?", $buyer_id);
$new_messages = getCount($conn, "
    SELECT COUNT(*) FROM product_inquiries 
    WHERE user_id = ? AND sender_role = 'seller' AND read_status = 'unread'
", $buyer_id);

// 💰 Monthly Spending
$monthly_stmt = $conn->prepare("
    SELECT SUM(total_amount) 
    FROM orders 
    WHERE buyer_id = ? 
    AND payment_status = 'paid' 
    AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
    AND YEAR(created_at) = YEAR(CURRENT_DATE())
");
$monthly_stmt->bind_param("i", $buyer_id);
$monthly_stmt->execute();
$monthly_stmt->bind_result($monthly_spent);
$monthly_stmt->fetch();
$monthly_stmt->close();
$monthly_spent = $monthly_spent ?? 0;

// 🔗 Card Links
$links = [
    'total_orders' => 'orders',
    'delivered_orders' => 'orders/index.php?status=delivered',
    'pending_orders' => 'ordersindexphp.php?status=pending',
    'wishlist' => 'wishlist/index.php',
    'messages' => 'messages/index.php',
];

// 📷 Profile Picture
$profile_img = $profile['profile_picture'] && file_exists("uploads/profile_pics/" . $profile['profile_picture'])
    ? "uploads/profile_pics/" . $profile['profile_picture']
    : "assets/images/img1.jpg";
?>

<div class="container py-5">
  <h2 class="mb-4">Buyer Dashboard</h2>

  <!-- 🧾 Profile Summary -->
  <div class="card bg-light mb-4">
    <div class="card-body d-flex align-items-center">
      <img src="<?= $profile_img ?>" alt="Profile Image" class="rounded-circle me-4" style="width: 80px; height: 80px; object-fit: cover;">
      <div>
        <h5 class="card-title mb-2"><?= htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']) ?></h5>
        <p class="mb-0"><strong>Email:</strong> <?= htmlspecialchars($profile['email']) ?></p>
        <p><strong>State:</strong> <?= htmlspecialchars($profile['state']) ?></p>
        <div class="d-flex flex-wrap gap-2">
          <a href="profile.php" class="btn btn-sm btn-outline-primary">View Profile</a>
          <a href="edit_profile.php" class="btn btn-sm btn-outline-secondary">Edit Profile</a>
          <a href="cart/" class="btn btn-sm btn-outline-success">View Cart</a>
        </div>
      </div>
    </div>
  </div>

  <!-- 📊 Stats Cards -->
  <div class="row g-3 g-md-4 mb-5 row-cols-2 row-cols-md-3">
  <div class="col">
      <div class="card text-white bg-dark shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title">💰 Monthly Spend</h5>
          <p class="display-6">₦<?= number_format($monthly_spent, 2) ?></p>
        </div>
      </div>
    </div>
  
    <div class="col">
      <a href="<?= $links['total_orders'] ?>" class="text-decoration-none">
        <div class="card text-white bg-primary shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title">🛒 Total Orders</h5>
            <p class="display-6"><?= $total_orders ?></p>
          </div>
        </div>
      </a>
    </div>

    <div class="col">
      <a href="<?= $links['delivered_orders'] ?>" class="text-decoration-none">
        <div class="card text-white bg-success shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title">📦 Delivered Orders</h5>
            <p class="display-6"><?= $delivered_orders ?></p>
          </div>
        </div>
      </a>
    </div>

    <div class="col">
      <a href="<?= $links['pending_orders'] ?>" class="text-decoration-none">
        <div class="card text-white bg-warning shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title">⏳ Pending Orders</h5>
            <p class="display-6"><?= $pending_orders ?></p>
          </div>
        </div>
      </a>
    </div>

    <div class="col">
      <a href="<?= $links['wishlist'] ?>" class="text-decoration-none">
        <div class="card text-white bg-danger shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title">💖 Wishlist</h5>
            <p class="display-6"><?= $total_wishlist ?></p>
          </div>
        </div>
      </a>
    </div>

    <div class="col">
      <a href="<?= $links['messages'] ?>" class="text-decoration-none">
        <div class="card text-white bg-info shadow-sm h-100">
          <div class="card-body">
            <h5 class="card-title">📩 New Messages</h5>
            <p class="display-6"><?= $new_messages ?></p>
          </div>
        </div>
      </a>
    </div>

    
  </div>
</div>

<?php include 'includes/footer.php'; ?>
