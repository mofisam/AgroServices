<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 🔄 Fetch Logs
$logs = $conn->query("
    SELECT w.id, w.amount, w.bank_name, w.account_number, w.account_name, w.status, 
           u.first_name, u.last_name, w.created_at, w.processed_at 
    FROM withdrawal_requests w 
    JOIN users u ON w.user_id = u.id 
    WHERE w.status != 'pending'
    ORDER BY w.processed_at DESC
");
?>

<div class="container py-5">
    <h2 class="mb-4">📜 Withdrawal Transaction Logs</h2>

    <div class="d-flex justify-content-end mb-3">
        <a href="export_withdrawals.php" class="btn btn-success">📄 Export to CSV</a>
    </div>

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
                    <th>Processed Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($logs->num_rows > 0): ?>
                    <?php while ($row = $logs->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['bank_name']) ?></td>
                            <td><?= htmlspecialchars($row['account_number']) ?></td>
                            <td><?= htmlspecialchars($row['account_name']) ?></td>
                            <td>₦<?= number_format($row['amount'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $row['status'] === 'approved' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></td>
                            <td><?= $row['processed_at'] ? date('Y-m-d H:i', strtotime($row['processed_at'])) : 'N/A' ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">No transactions found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
