<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle POST (Approval/Rejection)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['action'])) {
    $request_id = (int)$_POST['request_id'];
    $action = $_POST['action'];

    // Fetch withdrawal + bank + recipient info
    $stmt = $conn->prepare("
        SELECT wr.id, wr.user_id, wr.amount, ba.recipient_code, 
               wr.bank_name, wr.account_number, wr.account_name,
               u.first_name, u.last_name
        FROM withdrawal_requests wr
        JOIN bank_accounts ba ON ba.user_id = wr.user_id
        JOIN users u ON u.id = wr.user_id
        WHERE wr.id = ? AND wr.status = 'pending'
    ");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$result) {
        $_SESSION['alert'] = ['type' => 'danger', 'message' => 'Invalid request or already processed.'];
        header("Location: withdrawal_approval.php");
        exit();
    }

    if ($action === 'approve') {
        if (!$result['recipient_code']) {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => 'No Paystack recipient code found for seller.'];
            header("Location: withdrawal_approval.php");
            exit();
        }

        $paystack_secret_key = 'sk_test_41008269e1c6f30a68e89226ebe8bf9628c9e3ae'; // Replace with production key for live environment
        $amount_kobo = (int)($result['amount'] * 100);

        $payload = [
            'source' => 'balance',
            'amount' => $amount_kobo,
            'recipient' => $result['recipient_code'],
            'reason' => 'Seller withdrawal from F and V Agro Services'
        ];

        $ch = curl_init("https://api.paystack.co/transfer");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $paystack_secret_key",
            "Content-Type: application/json",
            "Cache-Control: no-cache"
        ]);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            $_SESSION['alert'] = ['type' => 'danger', 'message' => "Curl Error: $err"];
            header("Location: withdrawal_approval.php");
            exit();
        }

        $transfer_response = json_decode($response, true);
        
        if (!$transfer_response['status']) {
            $error_msg = $transfer_response['message'] ?? 'Unknown Paystack error';
            $_SESSION['alert'] = ['type' => 'danger', 'message' => "Paystack Error: $error_msg"];
            header("Location: withdrawal_approval.php");
            exit();
        }

        // Update withdrawal request with transfer reference
        $reference = $transfer_response['data']['reference'];
        $update = $conn->prepare("
            UPDATE withdrawal_requests 
            SET status = 'approved', 
                transfer_reference = ?,
                processed_at = NOW()
            WHERE id = ?
        ");
        $update->bind_param("si", $reference, $request_id);
        $update->execute();
        $update->close();

        $_SESSION['alert'] = [
            'type' => 'success', 
            'message' => "Withdrawal approved and money sent. Reference: $reference"
        ];
        header("Location: withdrawal_approval.php");
        exit();

    } elseif ($action === 'reject') {
        $update = $conn->prepare("
            UPDATE withdrawal_requests 
            SET status = 'rejected',
                processed_at = NOW()
            WHERE id = ?
        ");
        $update->bind_param("i", $request_id);
        $update->execute();
        $update->close();

        $_SESSION['alert'] = ['type' => 'warning', 'message' => 'Withdrawal request rejected.'];
        header("Location: withdrawal_approval.php");
        exit();
    }
}

// Search + List Requests
$requests = $conn->query("
    SELECT w.id, w.amount, w.bank_name, w.account_number, w.account_name, 
           w.status, w.created_at, w.processed_at, w.transfer_reference,
           u.first_name, u.last_name
    FROM withdrawal_requests w
    JOIN users u ON w.user_id = u.id
    ORDER BY w.created_at DESC
");

// Display alert if set
if (isset($_SESSION['alert'])) {
    echo "<div class='alert alert-{$_SESSION['alert']['type']}'>{$_SESSION['alert']['message']}</div>";
    unset($_SESSION['alert']);
}

include '../includes/header.php';
?>

<div class="container py-5">
    <h2 class="mb-4">🏦 Withdrawal Approval Panel</h2>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Seller</th>
                <th>Amount</th>
                <th>Bank</th>
                <th>Account #</th>
                <th>Name</th>
                <th>Status</th>
                <th>Requested</th>
                <th>Processed</th>
                <th>Reference</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $requests->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                    <td>₦<?= number_format($row['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($row['bank_name']) ?></td>
                    <td><?= htmlspecialchars($row['account_number']) ?></td>
                    <td><?= htmlspecialchars($row['account_name']) ?></td>
                    <td>
                        <span class="badge bg-<?= 
                            $row['status'] === 'approved' ? 'success' : 
                            ($row['status'] === 'rejected' ? 'danger' : 'warning') ?>">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </td>
                    <td><?= date("M d, Y H:i", strtotime($row['created_at'])) ?></td>
                    <td><?= $row['processed_at'] ? date("M d, Y H:i", strtotime($row['processed_at'])) : 'N/A' ?></td>
                    <td><?= $row['transfer_reference'] ?? 'N/A' ?></td>
                    <td>
                        <?php if ($row['status'] === 'pending'): ?>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to approve this withdrawal?');">
                                <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                                <button name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                            </form>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to reject this withdrawal?');">
                                <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                                <button name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        <?php else: ?>
                            <span class="text-muted">Processed</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>