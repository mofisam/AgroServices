<?php 
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit();
}

$buyer_id = $_SESSION['user_id'];
$success = $error = "";

// 📝 **Handle Confirmation First (before any output)** 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_id = (int)$_POST['item_id'];

    // ✅ **Fetch item information**
    $item_info = $conn->prepare("
        SELECT 
            o.payment_reference, 
            oi.delivery_status, 
            oi.subtotal,
            p.seller_id
        FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
        JOIN products p ON p.id = oi.product_id
        WHERE oi.id = ?
    ");
    $item_info->bind_param("i", $item_id);
    $item_info->execute();
    $item_data = $item_info->get_result()->fetch_assoc();
    $payment_reference = $item_data['payment_reference'] ?? 'N/A';
    $delivery_status = $item_data['delivery_status'] ?? 'unknown';
    $subtotal = (float)$item_data['subtotal'];
    $seller_id = (int)$item_data['seller_id'];
    $item_info->close();

    if ($delivery_status === 'in-transit' || $delivery_status === 'delivered') {
        // ✅ **Begin Transaction**
        $conn->begin_transaction();
        try {
            // 🔄 **Update delivery status and confirmation timestamp**
            $update = $conn->prepare("
                UPDATE order_items 
                SET delivery_confirmed = 1, delivery_confirmed_at = NOW() 
                WHERE id = ? AND delivery_confirmed = 0
            ");
            $update->bind_param("i", $item_id);
            $update->execute();
            $update->close();

            // 💰 **Update Seller's Wallet - Move to Withdrawable**
            $update_wallet = $conn->prepare("
                UPDATE seller_wallets 
                SET withdrawable_balance = withdrawable_balance + ? 
                WHERE seller_id = ?
            ");
            $update_wallet->bind_param("di", $subtotal, $seller_id);
            $update_wallet->execute();
            $update_wallet->close();

            $conn->commit();

            // ✅ **Send Email Notification**
            $seller_info = $conn->prepare("
                SELECT u.email, ba.business_name
                FROM users u 
                JOIN business_accounts ba ON ba.user_id = u.id
                WHERE u.id = ?
            ");
            $seller_info->bind_param("i", $seller_id);
            $seller_info->execute();
            $result = $seller_info->get_result()->fetch_assoc();
            $seller_email = $result['email'];
            $business_name = $result['business_name'];

            $subject = "Order Item Delivery Confirmed by Buyer";
            $message = "
                Dear $business_name,<br><br>
                The buyer has confirmed receipt of an item from order reference <strong>$payment_reference</strong>.<br>
                Amount of ₦" . number_format($subtotal, 2) . " has been moved to your withdrawable balance.<br><br>
                <strong>F and V Agro Services</strong>
            ";

            include 'includes/email_template.php';
            sendEmail($seller_email, $subject, $message);

            // 🚀 **Redirect with success message**
            $_SESSION['success'] = "Delivery confirmed successfully.";
            header("Location: confirm_delivery.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to confirm delivery.";
        }
    } else {
        $error = "Delivery status is not valid for confirmation.";
    }
}

// Now include header after potential redirects
include 'includes/header.php';

// Check for success message in session
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// 📦 **Fetch Deliveries that need buyer confirmation**
$items = $conn->prepare("
    SELECT 
        oi.id AS item_id, 
        oi.order_id, 
        oi.product_id,
        oi.delivery_status, 
        oi.estimated_delivery_date,
        oi.delivery_confirmed,
        p.name AS product_name,
        o.payment_reference,
        ba.business_name
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    JOIN orders o ON o.id = oi.order_id
    JOIN users u ON u.id = p.seller_id
    JOIN business_accounts ba ON ba.user_id = u.id
    WHERE o.buyer_id = ? 
    AND oi.delivery_confirmed = 0
    AND (oi.delivery_status = 'in-transit' OR oi.delivery_status = 'delivered')
");
$items->bind_param("i", $buyer_id);
$items->execute();
$item_results = $items->get_result();
?>

<div class="container py-5">
    <h2 class="mb-4">📦 Confirm Item Delivery</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Reference</th>
                    <th>Product Name</th>
                    <th>Business Name</th>
                    <th>Delivery Status</th>
                    <th>Estimated Delivery</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $item_results->fetch_assoc()): ?>
                    <tr>
                        <td><?= $item['item_id'] ?></td>
                        <td><?= htmlspecialchars($item['payment_reference']) ?></td>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= htmlspecialchars($item['business_name']) ?></td>
                        <td>
                            <span class="badge <?= $item['delivery_status'] === 'delivered' ? 'bg-success' : 'bg-warning' ?>">
                                <?= ucfirst($item['delivery_status']) ?>
                            </span>
                        </td>
                        <td><?= $item['estimated_delivery_date'] ?: 'Not Set' ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to confirm receipt of this item?')">
                                <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm">Confirm Receipt</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
