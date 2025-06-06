<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login");
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
$delivered_orders = getCount($conn, "SELECT COUNT(*) FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.buyer_id = ? AND oi.delivery_status = 'delivered'", $buyer_id);
$pending_orders = getCount($conn, "SELECT COUNT(*) FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.buyer_id = ? AND oi.delivery_status != 'delivered'", $buyer_id);
$total_wishlist = getCount($conn, "SELECT COUNT(*) FROM wishlists WHERE user_id = ?", $buyer_id);
$new_messages = getCount($conn, "
    SELECT COUNT(*) FROM product_inquiries 
    WHERE user_id = ? AND sender_role = 'seller' AND read_status = 'unread'
", $buyer_id);

// 💰 Monthly Spending
$monthly_stmt = $conn->prepare("
    SELECT SUM(oi.subtotal) 
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE o.buyer_id = ? 
    AND o.payment_status = 'paid' 
    AND MONTH(o.created_at) = MONTH(CURRENT_DATE()) 
    AND YEAR(o.created_at) = YEAR(CURRENT_DATE())
");
$monthly_stmt->bind_param("i", $buyer_id);
$monthly_stmt->execute();
$monthly_stmt->bind_result($monthly_spent);
$monthly_stmt->fetch();
$monthly_stmt->close();
$monthly_spent = $monthly_spent ?? 0;

// 🔗 Card Links
$links = [
    'total_orders' => 'orders/index',
    'delivered_orders' => 'orders/index?status=delivered',
    'pending_orders' => 'orders/index?status=pending',
    'wishlist' => 'wishlist/index',
    'messages' => 'messages/index',
];

// 📷 Profile Picture
$profile_img = $profile['profile_picture'] && file_exists("uploads/profile_pics/" . $profile['profile_picture'])
    ? "uploads/profile_pics/" . $profile['profile_picture']
    : "assets/images/img1.jpg";

?>

<?php include 'includes/header.php'; ?>
<div class="container py-5">
    <!-- Page Header with Quick Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Buyer Dashboard</h2>
            <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($profile['first_name']) ?>!</p>
        </div>
        <div class="d-flex gap-2">
            <a href="edit_profile" class="btn btn-outline-primary">
                <i class="bi bi-pencil-square"></i> Edit Profile
            </a>
            <a href="message_admin" class="btn btn-outline-secondary">
                <i class="bi bi-headset"></i> Contact Admin
            </a>
        </div>
    </div>

    <!-- Profile Summary Card -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-2 text-center mb-3 mb-md-0">
                    <img src="<?= $profile_img ?>" alt="Profile Image" class="rounded-circle shadow" style="width: 100px; height: 100px; object-fit: cover;">
                </div>
                <div class="col-md-5">
                    <h4 class="mb-2"><?= htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']) ?></h4>
                    <p class="text-muted mb-1"><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($profile['email']) ?></p>
                    <p class="text-muted"><i class="bi bi-geo-alt me-2"></i><?= htmlspecialchars($profile['state']) ?></p>
                </div>
                <div class="col-md-5">
                    <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                        <a href="profile" class="btn btn-primary">
                            <i class="bi bi-person me-1"></i> View Profile
                        </a>
                        <a href="cart/index" class="btn btn-success">
                            <i class="bi bi-cart me-1"></i> View Cart
                        </a>
                        <a href="confirm_delivery" class="btn btn-warning">
                            <i class="bi bi-check-circle me-1"></i> Confirm Delivery
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Dashboard -->
    <div class="row g-4 mb-4">
        <!-- Monthly Spend -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase text-muted mb-2">Monthly Spend</h6>
                            <h3 class="mb-0">₦<?= number_format($monthly_spent, 2) ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-currency-exchange text-primary fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="orders/index" class="text-decoration-none small">
                            View spending history <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="col-md-6 col-lg-3">
            <a href="<?= $links['total_orders'] ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-uppercase text-muted mb-2">Total Orders</h6>
                                <h3 class="mb-0"><?= $total_orders ?></h3>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="bi bi-cart-check text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-light text-dark small">View all orders</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Delivered Orders -->
        <div class="col-md-6 col-lg-3">
            <a href="<?= $links['delivered_orders'] ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-uppercase text-muted mb-2">Delivered</h6>
                                <h3 class="mb-0"><?= $delivered_orders ?></h3>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="bi bi-check-circle text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-success bg-opacity-10 text-success small">Completed</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Pending Orders -->
        <div class="col-md-6 col-lg-3">
            <a href="<?= $links['pending_orders'] ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-uppercase text-muted mb-2">Pending</h6>
                                <h3 class="mb-0"><?= $pending_orders ?></h3>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="bi bi-hourglass-split text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-warning bg-opacity-10 text-warning small">In progress</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Second Row of Stats -->
    <div class="row g-4 mb-4">
        <!-- Wishlist -->
        <div class="col-md-6 col-lg-3">
            <a href="<?= $links['wishlist'] ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-uppercase text-muted mb-2">Wishlist</h6>
                                <h3 class="mb-0"><?= $total_wishlist ?></h3>
                            </div>
                            <div class="bg-danger bg-opacity-10 p-3 rounded">
                                <i class="bi bi-heart text-danger fs-4"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-danger bg-opacity-10 text-danger small">Saved items</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Messages -->
        <div class="col-md-6 col-lg-3">
            <a href="<?= $links['messages'] ?>" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="text-uppercase text-muted mb-2">Messages</h6>
                                <h3 class="mb-0"><?= $new_messages ?></h3>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="bi bi-chat-left-text text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <?php if ($new_messages > 0): ?>
                                <span class="badge bg-info text-white small">New messages</span>
                            <?php else: ?>
                                <span class="badge bg-light text-dark small">No new messages</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted mb-3">Quick Actions</h6>
                    <div class="d-grid gap-2">
                        <a href="products/index" class="btn btn-outline-primary text-start">
                            <i class="bi bi-search me-2"></i> Browse Products
                        </a>
                        <a href="orders/index" class="btn btn-outline-success text-start">
                            <i class="bi bi-list-check me-2"></i> View Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted mb-3">Need Help?</h6>
                    <div class="d-grid gap-2">
                        <a href="message_admin" class="btn btn-outline-danger text-start">
                            <i class="bi bi-headset me-2"></i> Contact Admin
                        </a>
                        <a href="faq" class="btn btn-outline-secondary text-start">
                            <i class="bi bi-question-circle me-2"></i> FAQ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <!--<div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i> Recent Activity</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Recent orders, messages, and other activity will appear here.</p>-->
            <!-- I will add dynamic content here later -->
            <!--<div class="text-center py-3">
                <a href="orders/index" class="btn btn-sm btn-outline-primary">
                    View All Activity
                </a>
            </div>
        </div>
    </div>-->
</div>

<?php include 'includes/footer.php'; ?>