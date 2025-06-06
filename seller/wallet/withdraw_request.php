<?php
session_start();
include '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../../login");
    exit();
}
include '../../includes/header.php';

$seller_id = $_SESSION['user_id'];
$success = $error = "";

// 🔄 Fetch Wallet Balances
$wallet_stmt = $conn->prepare("SELECT withdrawable_balance, current_balance FROM seller_wallets WHERE seller_id = ?");
$wallet_stmt->bind_param("i", $seller_id);
$wallet_stmt->execute();
$wallet_stmt->bind_result($withdrawable_balance, $current_balance);
$wallet_stmt->fetch();
$wallet_stmt->close();

// 🔄 Fetch Bank Details
$bank_stmt = $conn->prepare("SELECT bank_name, account_number, account_name FROM bank_accounts WHERE user_id = ?");
$bank_stmt->bind_param("i", $seller_id);
$bank_stmt->execute();
$bank_details = $bank_stmt->get_result()->fetch_assoc();
$bank_stmt->close();

// 💸 Handle Withdrawal Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float) $_POST['amount'];

    if ($amount <= 0) {
        $error = "Amount must be greater than zero.";
    } elseif ($amount > $withdrawable_balance) {
        $error = "Insufficient withdrawable balance.";
    } else {
        // Begin transaction
        $conn->begin_transaction();
        try {
            // 🧮 Update seller_wallets
            $new_withdrawable_balance = $withdrawable_balance - $amount;
            $new_current_balance = $current_balance - $amount;

            $wallet_upd = $conn->prepare("UPDATE seller_wallets SET withdrawable_balance = ?, current_balance = ? WHERE seller_id = ?");
            $wallet_upd->bind_param("ddi", $new_withdrawable_balance, $new_current_balance, $seller_id);
            $wallet_upd->execute();
            $wallet_upd->close();

            // 💾 Insert withdrawal request
            $withdrawal = $conn->prepare("INSERT INTO withdrawal_requests (
                user_id, amount, bank_name, account_number, account_name, status
            ) VALUES (?, ?, ?, ?, ?, 'pending')");
            $withdrawal->bind_param("idsss", $seller_id, $amount, $bank_details['bank_name'], $bank_details['account_number'], $bank_details['account_name']);
            $withdrawal->execute();
            $withdrawal->close();

            // ✅ Mark eligible order items as withdrawn
            $mark_withdrawn = $conn->prepare("
                UPDATE order_items oi
                JOIN products p ON p.id = oi.product_id
                SET oi.withdrawn = 1
                WHERE p.seller_id = ? AND oi.withdrawn = 0
            ");
            $mark_withdrawn->bind_param("i", $seller_id);
            $mark_withdrawn->execute();
            $mark_withdrawn->close();

            $conn->commit();
            $success = "✅ Withdrawal request submitted successfully.";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "❌ Failed to process your withdrawal.";
        }
    }
    
}
?>

<div class="container py-5">
    <h2 class="mb-4">🏦 Withdrawal Request</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card p-4 shadow-sm bg-light">
        <h5 class="mb-3">💼 Current Balance: ₦<?= number_format($current_balance, 2) ?></h5>
        <h5 class="mb-3 text-success">🧾 Withdrawable: ₦<?= number_format($withdrawable_balance, 2) ?></h5>

        <?php if ($bank_details): ?>
            <p><strong>Bank:</strong> <?= htmlspecialchars($bank_details['bank_name']) ?></p>
            <p><strong>Account Number:</strong> <?= htmlspecialchars($bank_details['account_number']) ?></p>
            <p><strong>Account Name:</strong> <?= htmlspecialchars($bank_details['account_name']) ?></p>
        <?php else: ?>
            <div class="alert alert-warning">
                ⚠️ No bank account found. Please <a href="bank_account" class="alert-link">add a bank account</a>.
            </div>
        <?php endif; ?>

        <?php if ($bank_details): ?>
            <form method="POST" class="mt-3">
                <div class="mb-3">
                    <label for="amount" class="form-label">Withdrawal Amount (₦)</label>
                    <input type="number" name="amount" step="0.01" max="<?= $withdrawable_balance ?>" class="form-control" placeholder="Enter amount" required>
                </div>
                <button type="submit" class="btn btn-primary">💸 Submit Withdrawal</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
