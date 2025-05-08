<?php
session_start();
include '../config/db.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../includes/header.php';
// 📝 Variables
$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_date = $_GET['date'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// 🔎 Build Query
$query = "SELECT w.id, w.amount, w.bank_name, w.account_number, w.account_name, w.status, u.first_name, u.last_name, w.created_at 
          FROM withdrawal_requests w 
          JOIN users u ON w.user_id = u.id 
          WHERE 1 ";

if ($search) {
    $query .= "AND (u.first_name LIKE '%$search%' OR u.last_name LIKE '%$search%' OR w.bank_name LIKE '%$search%') ";
}

if ($filter_status) {
    $query .= "AND w.status = '$filter_status' ";
}

if ($filter_date) {
    $query .= "AND DATE(w.created_at) = '$filter_date' ";
}

$query .= "ORDER BY w.created_at DESC LIMIT $limit OFFSET $offset";

$requests = $conn->query($query);

// 🔄 Get Total Count for Pagination
$total_results = $conn->query("SELECT COUNT(*) as total FROM withdrawal_requests WHERE 1")->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);
?>

<div class="container py-5">
    <h2 class="mb-4">🏦 Withdrawal Approval Panel</h2>

    <!-- 🔎 Search and Filter -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Search by Name or Bank" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="pending" <?= $filter_status === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= $filter_status === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= $filter_status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($filter_date) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">🔎 Filter</button>
        </div>
        <div class="col-md-2">
            <a href="withdrawal_approval.php" class="btn btn-secondary w-100">🔄 Reset</a>
        </div>
    </form>

    <!-- 🗂️ Table -->
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Seller Name</th>
                    <th>Bank Name</th>
                    <th>Account Number</th>
                    <th>Account Name</th>
                    <th>Amount (₦)</th>
                    <th>Status</th>
                    <th>Request Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($requests->num_rows > 0): ?>
                    <?php while ($row = $requests->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['bank_name']) ?></td>
                            <td><?= htmlspecialchars($row['account_number']) ?></td>
                            <td><?= htmlspecialchars($row['account_name']) ?></td>
                            <td>₦<?= number_format($row['amount'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $row['status'] === 'approved' ? 'success' : ($row['status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></td>
                            <td>
                                <?php if ($row['status'] === 'pending'): ?>
                                    <form method="POST" style="display:inline-block">
                                        <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                    <form method="POST" style="display:inline-block">
                                        <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">Processed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">No requests found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 📌 Pagination -->
    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= $search ?>&status=<?= $filter_status ?>&date=<?= $filter_date ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<?php include '../includes/footer.php'; ?>
