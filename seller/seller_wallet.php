<?php 
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login");
    exit();
}
include '../includes/header.php';
$seller_id = $_SESSION['user_id'];

// 🔍 Fetch Business Info
$biz_stmt = $conn->prepare("SELECT business_name, business_address, payment_expiry FROM business_accounts WHERE user_id = ?");
$biz_stmt->bind_param("i", $seller_id);
$biz_stmt->execute();
$biz = $biz_stmt->get_result()->fetch_assoc();
$biz_stmt->close();

// 🧑 Seller Info
$user_stmt = $conn->prepare("SELECT first_name, last_name, profile_picture, last_login FROM users WHERE id = ?");
$user_stmt->bind_param("i", $seller_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

// 👁️ Monthly Product Impressions
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

// ⚠️ Out of Stock
$out_stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ? AND stock <= 0");
$out_stmt->bind_param("i", $seller_id);
$out_stmt->execute();
$out_stmt->bind_result($out_of_stock);
$out_stmt->fetch();
$out_stmt->close();

// ⭐ Avg Review This Month
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

// 💰 Fetch Current Balance
$wallet_stmt = $conn->prepare("SELECT current_balance FROM seller_wallets WHERE seller_id = ?");
$wallet_stmt->bind_param("i", $seller_id);
$wallet_stmt->execute();
$wallet_stmt->bind_result($current_balance);
$wallet_stmt->fetch();
$wallet_stmt->close();
$current_balance = $current_balance ?? 0;

// 🕓 Last Login
$last_login = $user['last_login'] ? date("F j, Y - h:i A", strtotime($user['last_login'])) : 'Never';

// 📈 Orders Trend
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

// 📷 Profile Picture
$profile_img = $user['profile_picture'] && file_exists("uploads/profile_pics/" . $user['profile_picture'])
    ? "uploads/profile_pics/" . $user['profile_picture']
    : "assets/images/img1.jpg";
?>

<div class="container py-5">
    <h2 class="mb-4">📊 Seller Dashboard</h2>

    <!-- 👤 Business Info -->
    <div class="card bg-light mb-4">
        <div class="card-body d-flex flex-column flex-md-row gap-4 align-items-center">
            <img src="<?= $profile_img ?>" class="rounded-circle" style="width: 90px; height: 90px; object-fit: cover;">
            <div>
                <h4><?= htmlspecialchars($biz['business_name']) ?></h4>
                <p class="mb-1"><strong>Business Address:</strong> <?= htmlspecialchars($biz['business_address']) ?></p>
                <p class="mb-1"><strong>Payment Expiry:</strong> <?= $biz['payment_expiry'] ? date("F j, Y", strtotime($biz['payment_expiry'])) : 'N/A' ?></p>
                <p class="mb-1"><strong>Last Login:</strong> <?= $last_login ?></p>
            </div>
        </div>
    </div>

    <!-- 🔢 Stats Cards -->
    <div class="row g-3 g-md-4 mb-5 row-cols-2 row-cols-md-3">
        <div class="col">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <h6 class="card-title">👁️ Impressions (This Month)</h6>
                    <p class="display-6"><?= $monthly_clicks ?></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <h6 class="card-title">⭐ Avg Rating (This Month)</h6>
                    <p class="display-6"><?= $avg_rating ?>/5</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card text-white bg-dark h-100">
                <div class="card-body">
                    <h6 class="card-title">💰 Current Balance</h6>
                    <p class="display-6">₦<?= number_format($current_balance, 2) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
