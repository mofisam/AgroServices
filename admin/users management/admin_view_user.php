<?php
include '../../config/db.php';

$user_id = $_GET["id"];

// Get user details along with activity counts
$stmt = $conn->prepare("SELECT u.*, 
                        (SELECT COUNT(*) FROM orders WHERE buyer_id = u.id) as order_count,
                        (SELECT COUNT(*) FROM products WHERE seller_id = u.id) as product_count,
                        (SELECT COUNT(*) FROM product_reviews WHERE user_id = u.id) as review_count
                        FROM users u WHERE u.id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get business account details if user is a seller
$business_info = [];
if ($user['role'] === 'seller') {
    $stmt = $conn->prepare("SELECT business_name, payment_status FROM business_accounts WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $business_info = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Format registration date
$registered_date = date('F j, Y', strtotime($user["created_at"]));
$registered_time = date('h:i A', strtotime($user["created_at"]));
?>

<div class="user-details-container">
    <div class="user-profile-header">
        <div class="profile-image-container">
            <img src="<?= $user['profile_picture'] ? '../../uploads/profile_pics/'.$user['profile_picture'] : '../../assets/images/default-user.png' ?>" 
                 alt="Profile Picture" class="profile-image">
        </div>
        <div class="profile-info">
            <h3 class="user-name"><?= htmlspecialchars($user["first_name"] . " " . $user["last_name"]) ?></h3>
            <span class="badge role-badge <?= $user['role'] ?>">
                <?= htmlspecialchars(ucfirst($user["role"])) ?>
            </span>
            <span class="badge status-badge <?= $user['status'] ?>">
                <?= htmlspecialchars(ucfirst($user["status"])) ?>
            </span>
        </div>
    </div>

    <div class="user-stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $user['order_count'] ?></div>
            <div class="stat-label">Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $user['product_count'] ?></div>
            <div class="stat-label">Products</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $user['review_count'] ?></div>
            <div class="stat-label">Reviews</div>
        </div>
    </div>

    <div class="user-details-section">
        <h4 class="section-title">Basic Information</h4>
        <div class="detail-row">
            <span class="detail-label">Email:</span>
            <span class="detail-value"><?= htmlspecialchars($user["email"]) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Phone:</span>
            <span class="detail-value"><?= !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not provided' ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Registered:</span>
            <span class="detail-value"><?= $registered_date ?> at <?= $registered_time ?></span>
        </div>
        <?php if(!empty($user['last_login'])): ?>
        <div class="detail-row">
            <span class="detail-label">Last Login:</span>
            <span class="detail-value"><?= date('F j, Y \a\t h:i A', strtotime($user['last_login'])) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <?php if($user['role'] === 'seller'): ?>
    <div class="user-details-section">
        <h4 class="section-title">Seller Information</h4>
        <div class="detail-row">
            <span class="detail-label">Business Name:</span>
            <span class="detail-value"><?= !empty($business_info['business_name']) ? htmlspecialchars($business_info['business_name']) : 'Not registered' ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Account Status:</span>
            <span class="detail-value"><?= !empty($business_info['payment_status']) ? htmlspecialchars(ucfirst($business_info['payment_status'])) : 'Not specified' ?></span>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.user-details-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.user-profile-header {
    display: flex;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.profile-image-container {
    margin-right: 20px;
}

.profile-image {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #f8f9fa;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.profile-info {
    flex: 1;
}

.user-name {
    margin: 0 0 5px 0;
    font-weight: 600;
}

.badge {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-right: 8px;
}

.role-badge {
    background-color: #e3f2fd;
    color: #1976d2;
}

.role-badge.admin {
    background-color: #ffebee;
    color: #d32f2f;
}

.role-badge.seller {
    background-color: #e8f5e9;
    color: #388e3c;
}

.status-badge {
    background-color: #f5f5f5;
    color: #616161;
}

.status-badge.active {
    background-color: #e8f5e9;
    color: #388e3c;
}

.status-badge.suspended {
    background-color: #ffebee;
    color: #d32f2f;
}

.user-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 25px;
}

.stat-card {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: #333;
}

.stat-label {
    font-size: 13px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.user-details-section {
    margin-bottom: 25px;
}

.section-title {
    font-size: 16px;
    color: #444;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 1px solid #eee;
}

.detail-row {
    display: flex;
    margin-bottom: 12px;
}

.detail-label {
    font-weight: 600;
    color: #555;
    width: 150px;
}

.detail-value {
    flex: 1;
    color: #333;
}
</style>