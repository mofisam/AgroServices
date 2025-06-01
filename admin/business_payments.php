<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 🗃️ **Pagination Setup**
$limit = 10;  // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// 🔎 **Filters and Search**
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$type = $_GET['type'] ?? '';

$query = "
    SELECT SQL_CALC_FOUND_ROWS 
        ba.*, 
        u.first_name, u.last_name, u.email, u.phone, 
        bpr.amount, bpr.type, bpr.reference, bpr.status AS transaction_status, bpr.created_at 
    FROM business_accounts ba
    JOIN users u ON ba.user_id = u.id
    LEFT JOIN business_payment_records bpr ON ba.user_id = bpr.user_id
    WHERE ba.payment_status = 'paid'
";

$conditions = [];
if ($search) {
    $conditions[] = "(u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%' OR u.email LIKE '%$search%' OR ba.business_name LIKE '%$search%')";
}
if ($status) {
    $conditions[] = "ba.registration_status = '$status'";
}
if ($type) {
    $conditions[] = "bpr.type = '$type'";
}

if ($conditions) {
    $query .= " AND " . implode(" AND ", $conditions);
}
$query .= " ORDER BY bpr.created_at DESC LIMIT $limit OFFSET $offset";

$payments = $conn->query($query);

// 🧮 **Get Total Rows for Pagination**
$total_results = $conn->query("SELECT FOUND_ROWS() AS total")->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);
?>

<div class="container py-5">
    <h2 class="mb-4">💳 Monitor Payments & Expiry Dates</h2>

    <!-- 🔍 Search and Filter -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Search by Name, Email, or Business" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Statuses</option>
                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="type" class="form-select">
                <option value="">All Types</option>
                <option value="registration" <?= $type === 'registration' ? 'selected' : '' ?>>Registration</option>
                <option value="renewal" <?= $type === 'renewal' ? 'selected' : '' ?>>Renewal</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">🔍 Search</button>
        </div>
        <div class="col-md-3">
            <a href="export_to_excel.php" class="btn btn-success w-100">📥 Export to Excel</a>
        </div>
    </form>

    <!-- 📋 Payment Table -->
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-success">
                <tr>
                    <th>Business Name</th>
                    <th>Owner</th>
                    <th>Amount (₦)</th>
                    <th>Type</th>
                    <th>Transaction Ref</th>
                    <th>Status</th>
                    <th>Expiry Date</th>
                    <th>Payment Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($payments->num_rows > 0): ?>
                    <?php while ($row = $payments->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['business_name']) ?></td>
                            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td>₦<?= number_format($row['amount'], 2) ?></td>
                            <td><?= ucfirst($row['type']) ?></td>
                            <td><?= htmlspecialchars($row['reference']) ?></td>
                            <td>
                                <?= $row['transaction_status'] === 'success' ? "<span class='badge bg-success'>Paid</span>" : "<span class='badge bg-warning'>Pending</span>" ?>
                            </td>
                            <td><?= $row['payment_expiry'] ? date("M d, Y", strtotime($row['payment_expiry'])) : 'N/A' ?></td>
                            <td><?= date("M d, Y h:i A", strtotime($row['created_at'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center">No payments found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 🔄 Pagination -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= $search ?>&status=<?= $status ?>&type=<?= $type ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<?php include '../includes/footer.php'; ?>
