<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login");
    exit();
}
include '../includes/header.php';
// 🔍 Filter Inputs
$keyword = $_GET['keyword'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['from'] ?? '';
$date_to = $_GET['to'] ?? '';

// 🔎 Build Dynamic Query
$conditions = [];
$params = [];
$types = "";

if ($keyword) {
    $conditions[] = "(o.payment_reference LIKE ? OR p.name LIKE ? OR CONCAT(o.first_name, ' ', o.last_name) LIKE ?)";
    $kw = "%$keyword%";
    array_push($params, $kw, $kw, $kw);
    $types .= "sss";
}

if ($status_filter) {
    $conditions[] = "oi.delivery_status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($date_from && $date_to) {
    $conditions[] = "DATE(oi.estimated_delivery_date) BETWEEN ? AND ?";
    array_push($params, $date_from, $date_to);
    $types .= "ss";
}

$query_conditions = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

$sql = "
    SELECT 
        o.payment_reference,
        o.first_name AS buyer_first_name,
        o.last_name AS buyer_last_name,
        oi.estimated_delivery_date,
        oi.delivery_status,
        oi.delivery_confirmed,
        oi.delivery_confirmed_at,
        p.name AS product_name,
        ba.business_name
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    JOIN products p ON p.id = oi.product_id
    JOIN business_accounts ba ON ba.user_id = p.seller_id
    $query_conditions
    ORDER BY oi.estimated_delivery_date DESC
";

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$deliveries = $stmt->get_result();
?>

<div class="container py-5">
    <h2 class="mb-4">📦 Deliveries Overview</h2>

    <!-- 🔍 Filter Form -->
    <form class="row g-3 mb-4" method="GET">
        <div class="col-md-3">
            <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" class="form-control" placeholder="Search ref, product, buyer">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="in-transit" <?= $status_filter === 'in-transit' ? 'selected' : '' ?>>In Transit</option>
                <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="from" value="<?= htmlspecialchars($date_from) ?>" class="form-control">
        </div>
        <div class="col-md-2">
            <input type="date" name="to" value="<?= htmlspecialchars($date_to) ?>" class="form-control">
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary w-100">Apply Filters</button>
        </div>
    </form>

    <!-- 📋 Delivery Table -->
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>Ref</th>
                    <th>Product</th>
                    <th>Buyer</th>
                    <th>Business Name</th>
                    <th>Est. Delivery</th>
                    <th>Status</th>
                    <th>Confirmed</th>
                    <th>Confirmed At</th>
                </tr>
            </thead>
            <tbody>
                <?php $chartData = []; ?>
                <?php while ($row = $deliveries->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['payment_reference']) ?></td>
                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                        <td><?= htmlspecialchars($row['buyer_first_name'] . ' ' . $row['buyer_last_name']) ?></td>
                        <td><?= htmlspecialchars($row['business_name']) ?></td>
                        <td><?= $row['estimated_delivery_date'] ?: 'Not Set' ?></td>
                        <td><span class="badge bg-<?= $row['delivery_status'] === 'delivered' ? 'success' : ($row['delivery_status'] === 'in-transit' ? 'info' : 'secondary') ?>">
                            <?= ucfirst($row['delivery_status']) ?>
                        </span></td>
                        <td><?= $row['delivery_confirmed'] ? '✅ Yes' : '❌ No' ?></td>
                        <td><?= $row['delivery_confirmed_at'] ?: '—' ?></td>
                    </tr>

                    <!-- 📈 Chart Data -->
                    <?php if ($row['estimated_delivery_date']): ?>
                        <?php $chartData[] = [
                            'label' => $row['payment_reference'],
                            'date' => $row['estimated_delivery_date'],
                            'status' => $row['delivery_status']
                        ]; ?>
                    <?php endif; ?>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>