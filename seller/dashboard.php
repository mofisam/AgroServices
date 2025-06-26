<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login");
    exit();
}

include '../includes/header.php';

$seller_id = $_SESSION['user_id'];

// Business Info
$biz_stmt = $conn->prepare("SELECT business_name, business_address, payment_expiry FROM business_accounts WHERE user_id = ?");
$biz_stmt->bind_param("i", $seller_id);
$biz_stmt->execute();
$biz = $biz_stmt->get_result()->fetch_assoc();
$biz_stmt->close();

// User Info
$user_stmt = $conn->prepare("SELECT first_name, last_name, profile_picture, last_login FROM users WHERE id = ?");
$user_stmt->bind_param("i", $seller_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

// Product Impressions
$stmt = $conn->prepare("
    SELECT COUNT(*) FROM product_clicks 
    WHERE product_id IN (SELECT id FROM products WHERE seller_id = ?)
    AND MONTH(clicked_at) = MONTH(CURDATE()) AND YEAR(clicked_at) = YEAR(CURDATE())
");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$stmt->bind_result($monthly_clicks);
$stmt->fetch();
$stmt->close();

// Out of Stock
$out_stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ? AND stock <= 0");
$out_stmt->bind_param("i", $seller_id);
$out_stmt->execute();
$out_stmt->bind_result($out_of_stock);
$out_stmt->fetch();
$out_stmt->close();

// Avg Rating
$review_stmt = $conn->prepare("
    SELECT ROUND(AVG(rating), 1) FROM product_reviews 
    WHERE product_id IN (SELECT id FROM products WHERE seller_id = ?)
    AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())
");
$review_stmt->bind_param("i", $seller_id);
$review_stmt->execute();
$review_stmt->bind_result($avg_rating);
$review_stmt->fetch();
$review_stmt->close();
$avg_rating = $avg_rating ?? 0;

// Wallet Balance
$wallet_stmt = $conn->prepare("SELECT current_balance FROM seller_wallets WHERE seller_id = ?");
$wallet_stmt->bind_param("i", $seller_id);
$wallet_stmt->execute();
$wallet_stmt->bind_result($current_balance);
$wallet_stmt->fetch();
$wallet_stmt->close();
$current_balance = $current_balance ?? 0;

// Product Count
$product_stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ?");
$product_stmt->bind_param("i", $seller_id);
$product_stmt->execute();
$product_stmt->bind_result($total_products);
$product_stmt->fetch();
$product_stmt->close();

// Last Login
$last_login = $user['last_login'] ? date("F j, Y - h:i A", strtotime($user['last_login'])) : 'Never';

// Order Trend
$monthlyOrders = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date("Y-m", strtotime("-$i months"));
    $delivered = $pending = 0;

    $stmt = $conn->prepare("
        SELECT delivery_status, COUNT(*) FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON p.id = oi.product_id
        WHERE p.seller_id = ? AND DATE_FORMAT(o.created_at, '%Y-%m') = ?
        GROUP BY delivery_status
    ");
    $stmt->bind_param("is", $seller_id, $month);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if ($row['delivery_status'] === 'delivered') $delivered += $row['COUNT(*)'];
        else $pending += $row['COUNT(*)'];
    }
    $stmt->close();
    $monthlyOrders[] = [
        'month' => date("M", strtotime($month)),
        'delivered' => $delivered,
        'pending' => $pending
    ];
}

// Profile Image
$profile_img = (isset($user['profile_picture']) && $user['profile_picture'] !== '' && file_exists("../uploads/profile_pics/" . $user['profile_picture']))
    ? "../uploads/profile_pics/" . $user['profile_picture']
    : "../assets/images/img1.jpg";
?>

<div class="container py-4">
    <!-- Page Header with Business Status -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Seller Dashboard</h2>
            <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></p>
        </div>
        <div class="d-flex gap-2">
            <div class="d-flex align-items-center bg-light rounded px-3 py-1">
                <span class="me-2">Business Status:</span>
                <span class="badge bg-<?= $biz['payment_expiry'] && strtotime($biz['payment_expiry']) > time() ? 'success' : 'danger' ?>">
                    <?= $biz['payment_expiry'] && strtotime($biz['payment_expiry']) > time() ? 'Active' : 'Expired' ?>
                </span>
            </div>
            <a href="edit_profile" class="btn btn-outline-primary">
                <i class="bi bi-pencil-square me-1"></i> Edit Profile
            </a>
        </div>
    </div>

    <!-- Business Profile Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-2 text-center mb-3 mb-md-0">
                    <a href="profile">
                        <img src="<?= $profile_img ?>" alt="Profile Image" class="rounded-circle shadow" style="width: 100px; height: 100px; object-fit: cover;">
                    </a>
                </div>
                <div class="col-md-5">
                    <h4 class="mb-2"><?= htmlspecialchars($biz['business_name']) ?></h4>
                    <p class="text-muted mb-1"><i class="bi bi-geo-alt me-2"></i><?= htmlspecialchars($biz['business_address']) ?></p>
                    <p class="text-muted mb-1"><i class="bi bi-calendar-check me-2"></i>Expires: <?= $biz['payment_expiry'] ? date("M j, Y", strtotime($biz['payment_expiry'])) : 'N/A' ?></p>
                    <p class="text-muted"><i class="bi bi-clock-history me-2"></i>Last Login: <?= $last_login ?></p>
                </div>
                <div class="col-md-5">
                    <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                        <a href="product" class="btn btn-primary">
                            <i class="bi bi-box-seam me-1"></i> My Products (<?= $total_products ?>)
                        </a>
                        <a href="wallet" class="btn btn-success">
                            <i class="bi bi-wallet2 me-1"></i> Wallet (₦<?= number_format($current_balance, 2) ?>)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Alert -->
    <?php if ($out_of_stock > 0): ?>
        <div class="alert alert-warning d-flex align-items-center mb-4">
            <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
            <div>
                <h5 class="alert-heading mb-1">Stock Alert!</h5>
                You have <?= $out_of_stock ?> product<?= $out_of_stock > 1 ? 's' : '' ?> out of stock.
                <a href="product" class="alert-link">Manage inventory</a> to avoid missed sales.
            </div>
        </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i> Quick Actions</h5>
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="product/" class="btn btn-outline-primary w-100 text-start">
                                <i class="bi bi-plus-circle me-2"></i> Add Product
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="manage_orders" class="btn btn-outline-success w-100 text-start">
                                <i class="bi bi-truck me-2"></i> Manage Orders
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="wallet/withdraw_request.php" class="btn btn-outline-info w-100 text-start">
                                <i class="bi bi-cash-coin me-2"></i> Request Withdrawal
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="wallet/bank_account.php" class="btn btn-outline-secondary w-100 text-start">
                                <i class="bi bi-bank me-2"></i> Bank Account
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="admin_support/" class="btn btn-outline-dark w-100 text-start">
                                <i class="bi bi-headset me-2"></i> Contact Admin
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="renew_account" class="btn btn-outline-danger w-100 text-start">
                                <i class="bi bi-arrow-repeat me-2"></i> Renew Business
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Performance Stats -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="bi bi-graph-up-arrow text-primary me-2"></i> Performance Overview</h5>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Wallet Balance</h6>
                                    <h3 class="mb-0">₦<?= number_format($current_balance, 2) ?></h3>
                                    <small class="text-muted">Available funds</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Monthly Impressions</h6>
                                    <h3 class="mb-0"><?= $monthly_clicks ?></h3>
                                    <small class="text-muted">Product views</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Average Rating</h6>
                                    <h3 class="mb-0"><?= $avg_rating ?><small class="text-muted fs-6">/5</small></h3>
                                    <small class="text-muted">This month</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Total Products</h6>
                                    <h3 class="mb-0"><?= $total_products ?></h3>
                                    <small class="text-muted">In your catalog</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Trends Chart -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i> Order Trends (Last 6 Months)</h5>
                <a href="orders/manage_orders" class="btn btn-sm btn-outline-primary">
                    View All Orders
                </a>
            </div>
        </div>
        <div class="card-body">
            <canvas id="ordersChart" height="130"></canvas>
        </div>
    </div>

    <!-- Recent Activity -->
    <!--<div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i> Recent Activity</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Recent orders, product updates, and messages will appear here.</p>-->
            <!-- You can add dynamic content here later -->
            <!--<div class="text-center py-3">
                <a href="activity_log" class="btn btn-sm btn-outline-primary">
                    View Full Activity Log
                </a>
            </div>
        </div>
    </div>-->
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('ordersChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($monthlyOrders, 'month')) ?>,
        datasets: [
            {
                label: 'Delivered Orders',
                backgroundColor: '#28a745',
                data: <?= json_encode(array_column($monthlyOrders, 'delivered')) ?>
            },
            {
                label: 'Pending Orders',
                backgroundColor: '#ffc107',
                data: <?= json_encode(array_column($monthlyOrders, 'pending')) ?>
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { 
                position: 'top',
                labels: {
                    boxWidth: 12,
                    padding: 20
                }
            },
            tooltip: {
                mode: 'index',
                intersect: false
            }
        },
        scales: {
            y: { 
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>