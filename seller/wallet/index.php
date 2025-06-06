<?php
session_start();
include '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../../login");
    exit();
}
include '../../includes/header.php';

$seller_id = $_SESSION['user_id'];

// 🔄 Fetch Wallet Balances
$wallet_stmt = $conn->prepare("
    SELECT current_balance, total_earned, withdrawable_balance 
    FROM seller_wallets 
    WHERE seller_id = ?
");
$wallet_stmt->bind_param("i", $seller_id);
$wallet_stmt->execute();
$wallet_stmt->bind_result($balance, $total_earned, $withdrawable_balance);
$wallet_stmt->fetch();
$wallet_stmt->close();

// Fallbacks
$balance = $balance ?? 0;
$total_earned = $total_earned ?? 0;
$withdrawable_balance = $withdrawable_balance ?? 0;

// 📜 Fetch transaction history
$transactions_stmt = $conn->prepare("
    SELECT amount, type, description, created_at 
    FROM wallet_transactions 
    WHERE seller_id = ? 
    ORDER BY created_at DESC
");
$transactions_stmt->bind_param("i", $seller_id);
$transactions_stmt->execute();
$transactions_result = $transactions_stmt->get_result();
?>

<div class="container py-5">
    <h2 class="mb-4">💼 Wallet Overview</h2>

    <!-- 💰 Wallet Summary -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card bg-success text-white shadow h-100">
                <div class="card-body">
                    <h5>Current Balance</h5>
                    <p class="display-6">₦<?= number_format($balance, 2) ?></p>
                </div>
            </div>
        </div>
        <!-- Total Earned. (I will decide omn what to do on it later)
        <div class="col-md-4 mb-3">
            <div class="card bg-primary text-white shadow h-100">
                <div class="card-body">
                    <h5>Total Earned</h5>
                    <p class="display-6">₦<?= number_format($total_earned, 2) ?></p>
                </div>
            </div>
        </div>
        -->
        <div class="col-md-6 mb-3">
            <div class="card bg-primary text-white shadow h-100">
                <div class="card-body">
                    <h5>Withdrawable Balance</h5>
                    <p class="display-6">₦<?= number_format($withdrawable_balance, 2) ?></p>
                    <a href="withdraw_request" class="btn btn-light btn-sm">Request Withdrawal</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 📑 Transaction History -->
    <div class="card shadow mb-5">
        <div class="card-body">
            <h5 class="card-title mb-3">🧾 Transaction History</h5>
            <?php if ($transactions_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th class="text-end">Amount (₦)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($tx = $transactions_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= date("M j, Y H:i", strtotime($tx['created_at'])) ?></td>
                                    <td>
                                        <span class="badge <?= $tx['type'] === 'credit' ? 'bg-success' : 'bg-danger' ?>">
                                            <?= ucfirst($tx['type']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($tx['description']) ?></td>
                                    <td class="text-end">₦<?= number_format($tx['amount'], 2) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No transactions yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>