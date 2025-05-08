<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: ../../login.php");
    exit();
}
include 'includes/header.php';
$buyer_id = $_SESSION['user_id'];
$success = $error = "";

// 🔄 Fetch Pending Deliveries
$orders = $conn->prepare("
    SELECT id, payment_reference, estimated_delivery_date, delivery_status 
    FROM orders 
    WHERE buyer_id = ? AND delivery_status = 'in-transit'
");
$orders->bind_param("i", $buyer_id);
$orders->execute();
$order_results = $orders->get_result();

// ✅ Handle Confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];

    $update = $conn->prepare("
        UPDATE orders 
        SET delivery_status = 'delivered', delivery_confirmed = 1, delivery_confirmed_at = NOW() 
        WHERE id = ? AND delivery_status = 'in-transit'
    ");
    $update->bind_param("i", $order_id);

    if ($update->execute()) {
        $success = "Delivery confirmed successfully.";
    } else {
        $error = "Failed to confirm delivery.";
    }
    $update->close();
}
?>

<div class="container py-5">
    <h2 class="mb-4">📦 Confirm Delivery</h2>

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
                    <th>Estimated Delivery Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $order_results->fetch_assoc()): ?>
                    <tr>
                        <td><?= $order['id'] ?></td>
                        <td><?= htmlspecialchars($order['payment_reference']) ?></td>
                        <td><?= $order['estimated_delivery_date'] ?: 'Not Set' ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <button type="submit" class="btn btn-success btn-sm">Confirm Delivery</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
