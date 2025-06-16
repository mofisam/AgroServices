<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login");
    exit();
}
include '../includes/header.php';

// Get admin user info
$user_stmt = $conn->prepare("SELECT first_name, last_name, email, profile_picture, last_login FROM users WHERE id = ?");
$user_stmt->bind_param("i", $_SESSION['user_id']);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

// Format last login
$last_login = $user['last_login'] ? date("F j, Y - h:i A", strtotime($user['last_login'])) : 'Never';

// Profile image
$profile_img = ($user['profile_picture'] && file_exists("../uploads/profile_pics/" . $user['profile_picture']))
    ? "../uploads/profile_pics/" . $user['profile_picture']
    : "../assets/images/img1.jpg";

// 🔁 Helper to get counts
function getCount($conn, $sql) {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count ?? 0;
}

// 📊 Core Metrics
$total_buyers   = getCount($conn, "SELECT COUNT(*) FROM users WHERE role = 'buyer'");
$total_sellers  = getCount($conn, "SELECT COUNT(*) FROM users WHERE role = 'seller'");
$total_products = getCount($conn, "SELECT COUNT(*) FROM products");
$total_orders   = getCount($conn, "SELECT COUNT(*) FROM orders");
$total_withdrawals = getCount($conn, "SELECT COUNT(*) FROM withdrawal_requests WHERE status = 'pending'");
$delivered      = getCount($conn, "SELECT COUNT(*) FROM order_items WHERE delivery_status = 'delivered'");
$pending        = getCount($conn, "SELECT COUNT(*) FROM order_items WHERE delivery_status != 'delivered'");

// 🧾 Business Expiry
$expired_businesses = getCount($conn, "SELECT COUNT(*) FROM business_accounts WHERE payment_expiry < CURDATE()");
$active_products    = getCount($conn, "SELECT COUNT(*) FROM products WHERE status = 'active'");
$inactive_products  = getCount($conn, "SELECT COUNT(*) FROM products WHERE status = 'inactive'");
$new_users_month    = getCount($conn, "SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");

// 💰 Monthly Sales
$sales_stmt = $conn->prepare("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
$sales_stmt->execute();
$sales_stmt->bind_result($monthly_sales);
$sales_stmt->fetch();
$sales_stmt->close();
$monthly_sales = $monthly_sales ?? 0;

// 📈 Order Trend
$order_trend = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $stmt = $conn->prepare("
        SELECT 
            SUM(CASE WHEN delivery_status = 'delivered' THEN 1 ELSE 0 END),
            SUM(CASE WHEN delivery_status != 'delivered' THEN 1 ELSE 0 END)
        FROM order_items 
        WHERE DATE_FORMAT(updated_at, '%Y-%m') = ?
    ");
    $stmt->bind_param("s", $month);
    $stmt->execute();
    $stmt->bind_result($delivered_count, $pending_count);
    $stmt->fetch();
    $stmt->close();
    $order_trend[] = [
        'month' => date("M", strtotime($month)),
        'delivered' => (int)$delivered_count,
        'pending' => (int)$pending_count
    ];
}
?>

<div class="container py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Admin Dashboard</h2>
            <p class="text-muted mb-0">System overview and quick actions</p>
        </div>
        <div class="d-flex gap-2">
            <a href="set_registration_fee" class="btn btn-outline-primary">
                <i class="bi bi-gear me-1"></i> System Settings
            </a>
            <a href="admin_logs" class="btn btn-outline-secondary">
                <i class="bi bi-journal-text me-1"></i> View Logs
            </a>
        </div>
    </div>

    <!-- 👤 Admin Profile Section -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-2 text-center mb-3 mb-md-0">
                <img src="<?= $profile_img ?>" alt="Admin Profile" class="rounded-circle shadow" style="width: 100px; height: 100px; object-fit: cover;">
                <div class="mt-2">
                    <span class="badge bg-primary">Administrator</span>
                </div>
            </div>
            <div class="col-md-5">
                <h4 class="mb-2"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h4>
                <p class="text-muted mb-1"><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($user['email']) ?></p>
                <p class="text-muted mb-1"><i class="bi bi-calendar me-2"></i>Last Login: <?= $last_login ?></p>
                <p class="text-muted"><i class="bi bi-shield-lock me-2"></i>Super Admin Access</p>
            </div>
            <div class="col-md-5">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                    <a href="profile" class="btn btn-primary">
                        <i class="bi bi-person me-1"></i> View Profile
                    </a>
                    <a href="edit_profile" class="btn btn-outline-primary">
                        <i class="bi bi-pencil-square me-1"></i> Edit Profile
                    </a>
                    <a href="messages/index" class="btn btn-success">
                        <i class="bi bi-chat-left-text me-1"></i> Messages
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Quick Navigation -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i> Quick Actions</h5>
            <div class="row g-2">
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="users management/manage_users" class="btn btn-outline-dark w-100 text-start">
                        <i class="bi bi-people me-2"></i> Manage Users
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="products" class="btn btn-outline-info w-100 text-start">
                        <i class="bi bi-box-seam me-2"></i> Products
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="orders" class="btn btn-outline-primary w-100 text-start">
                        <i class="bi bi-cart-check me-2"></i> Orders
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="withdrawal_approval" class="btn btn-outline-warning w-100 text-start">
                        <i class="bi bi-cash-coin me-2"></i> Withdrawals
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="business_payments" class="btn btn-outline-success w-100 text-start">
                        <i class="bi bi-building me-2"></i> Business Accounts
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="delivery_overview" class="btn btn-outline-secondary w-100 text-start">
                        <i class="bi bi-truck me-2"></i> Delivery Overview
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="product categories/index" class="btn btn-outline-primary w-100 text-start">
                        <i class="bi bi-tags me-2"></i>Product Categories
                    </a>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="contact_messages" class="btn btn-outline-danger w-100 text-start">
                        <i class="bi bi-envelope me-2"></i> Contact Messages
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- System Metrics -->
    <div class="row g-4 mb-4">
        <!-- User Metrics -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i> User Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="card bg-primary bg-opacity-10 border-0 h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Total Buyers</h6>
                                    <h3 class="mb-0"><?= $total_buyers ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-success bg-opacity-10 border-0 h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Total Sellers</h6>
                                    <h3 class="mb-0"><?= $total_sellers ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card bg-info bg-opacity-10 border-0">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">New Users This Month</h6>
                                    <h3 class="mb-0"><?= $new_users_month ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Metrics -->
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i> Product Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="card bg-dark bg-opacity-10 border-0 h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Total Products</h6>
                                    <h3 class="mb-0"><?= $total_products ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-success bg-opacity-10 border-0 h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Active</h6>
                                    <h3 class="mb-0"><?= $active_products ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-secondary bg-opacity-10 border-0 h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Inactive</h6>
                                    <h3 class="mb-0"><?= $inactive_products ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-danger bg-opacity-10 border-0 h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Expired Businesses</h6>
                                    <h3 class="mb-0"><?= $expired_businesses ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Metrics -->
        <div class="col-md-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="bi bi-cart-check me-2"></i> Order Metrics</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="card bg-info bg-opacity-10 border-0 h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Total Orders</h6>
                                    <h3 class="mb-0"><?= $total_orders ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-success bg-opacity-10 border-0 h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Delivered</h6>
                                    <h3 class="mb-0"><?= $delivered ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-warning bg-opacity-10 border-0 h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Pending</h6>
                                    <h3 class="mb-0"><?= $pending ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-secondary bg-opacity-10 border-0 h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Monthly Sales</h6>
                                    <h3 class="mb-0">₦<?= number_format($monthly_sales, 2) ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Metrics -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i> Financial Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="card bg-primary bg-opacity-10 border-0 h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Pending Withdrawals</h6>
                                    <h3 class="mb-0"><?= $total_withdrawals ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-success bg-opacity-10 border-0 h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-2">Monthly Sales</h6>
                                    <h3 class="mb-0">₦<?= number_format($monthly_sales, 2) ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-warning mb-0">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong><?= $expired_businesses ?> expired business accounts</strong> need attention
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Trends Chart -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i> Order Trends</h5>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-secondary active">6 Months</button>
                        <button class="btn btn-sm btn-outline-secondary">1 Year</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="ordersChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i> Recent System Activity</h5>
            <a href="admin_logs" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body">
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-person-plus-fill text-success me-2"></i>
                        <span>5 new users registered today</span>
                    </div>
                    <small class="text-muted">2 hours ago</small>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-cart-check-fill text-primary me-2"></i>
                        <span>12 new orders received</span>
                    </div>
                    <small class="text-muted">5 hours ago</small>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-cash-coin text-warning me-2"></i>
                        <span>3 withdrawal requests pending</span>
                    </div>
                    <small class="text-muted">1 day ago</small>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                        <span>System backup completed</span>
                    </div>
                    <small class="text-muted">2 days ago</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('ordersChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($order_trend, 'month')) ?>,
        datasets: [
            {
                label: 'Delivered Orders',
                borderColor: '#28a745',
                backgroundColor: '#28a74510',
                borderWidth: 2,
                tension: 0.3,
                fill: true,
                data: <?= json_encode(array_column($order_trend, 'delivered')) ?>
            },
            {
                label: 'Pending Orders',
                borderColor: '#ffc107',
                backgroundColor: '#ffc10710',
                borderWidth: 2,
                tension: 0.3,
                fill: true,
                data: <?= json_encode(array_column($order_trend, 'pending')) ?>
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    boxWidth: 12,
                    padding: 20,
                    usePointStyle: true
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
                    precision: 0
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