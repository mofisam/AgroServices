<?php
session_start();
include '../../config/db.php';
include '../../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../../login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];
$success = $error = "";

// 🔄 Fetch Wallet Balance
$wallet_stmt = $conn->prepare("SELECT current_balance FROM seller_wallets WHERE seller_id = ?");
$wallet_stmt->bind_param("i", $seller_id);
$wallet_stmt->execute();
$wallet_stmt->bind_result($wallet_balance);
$wallet_stmt->fetch();
$wallet_stmt->close();

// 🔄 Fetch Bank Details
$bank_stmt = $conn->prepare("SELECT bank_name, account_number, account_name FROM bank_accounts WHERE user_id = ?");
$bank_stmt->bind_param("i", $seller_id);
$bank_stmt->execute();
$bank_details = $bank_stmt->get_result()->fetch_assoc();
$bank_stmt->close();

// 💡 Handle Withdrawal Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float) $_POST['amount'];

    if ($amount <= 0) {
        $error = "Amount must be greater than zero.";
    } elseif ($amount > $wallet_balance) {
        $error = "Insufficient balance.";
    } else {
        // Begin Transaction
        $conn->begin_transaction();
        try {
            // 🔄 Update Wallet
            $new_balance = $wallet_balance - $amount;
            $update_wallet = $conn->prepare("UPDATE seller_wallets SET current_balance = ? WHERE seller_id = ?");
            $update_wallet->bind_param("di", $new_balance, $seller_id);
            $update_wallet->execute();
            $update_wallet->close();

            // ➕ Insert Withdrawal Request
            $insert_withdrawal = $conn->prepare("INSERT INTO withdrawal_requests (
                user_id, amount, bank_name, account_number, account_name, status
            ) VALUES (?, ?, ?, ?, ?, 'pending')");
            $insert_withdrawal->bind_param("idsss", $seller_id, $amount, $bank_details['bank_name'], $bank_details['account_number'], $bank_details['account_name']);
            $insert_withdrawal->execute();
            $insert_withdrawal->close();

            // Commit Transaction
            $conn->commit();
            $success = "Withdrawal request submitted successfully. Processing...";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to process your withdrawal. Please try again.";
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
        <h5 class="mb-3">💰 Wallet Balance: ₦<?= number_format($wallet_balance, 2) ?></h5>

        <?php if ($bank_details): ?>
            <p><strong>Bank:</strong> <?= htmlspecialchars($bank_details['bank_name']) ?></p>
            <p><strong>Account Number:</strong> <?= htmlspecialchars($bank_details['account_number']) ?></p>
            <p><strong>Account Name:</strong> <?= htmlspecialchars($bank_details['account_name']) ?></p>
        <?php else: ?>
            <div class="alert alert-warning">⚠️ No bank account found. Please set up your <a href="bank_account.php">Bank Account</a>.</div>
        <?php endif; ?>

        <?php if ($bank_details): ?>
            <form method="POST" class="mt-3">
                <div class="mb-3">
                    <label class="form-label">Withdrawal Amount (₦)</label>
                    <input type="number" name="amount" step="0.01" class="form-control" placeholder="Enter amount to withdraw" required>
                </div>
                <button type="submit" class="btn btn-primary">💸 Request Withdrawal</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
