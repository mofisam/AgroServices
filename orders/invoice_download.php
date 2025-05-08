<?php
session_start();
require '../vendor/autoload.php'; // if using Composer
include '../config/db.php';

use Dompdf\Dompdf;

// 🔐 Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    die("Access denied");
}

$ref = $_GET['ref'] ?? '';
$buyer_id = $_SESSION['user_id'];

// Fetch order
$stmt = $conn->prepare("SELECT * FROM orders WHERE payment_reference = ? AND buyer_id = ?");
$stmt->bind_param("si", $ref, $buyer_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found.");
}

// Fetch order items
$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order['id']);
$stmt->execute();
$items = $stmt->get_result();

// Start HTML output
ob_start();

$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Invoice - <?= $ref ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #000; padding: 6px; text-align: left; }
        .footer { text-align: center; font-size: 11px; color: #888; }
    </style>
</head>
<body>

<div class="header">
    <h2>Invoice</h2>
    <p>Order Reference: <?= htmlspecialchars($ref) ?><br>
       Date: <?= date("M d, Y h:i A", strtotime($order['created_at'])) ?><br>
       Payment Status: <?= htmlspecialchars(ucfirst($order['payment_status'])) ?>
    </p>
</div>

<h4>Delivery Info</h4>
<p>
    <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?><br>
    <?= htmlspecialchars($order['email']) ?><br>
    <?= htmlspecialchars($order['phone']) ?><br>
    <?= htmlspecialchars($order['shipping_address']) ?><br>
    <?= htmlspecialchars($order['state']) ?>
</p>

<h4>Items Ordered</h4>
<table class="table">
    <thead>
        <tr>
            <th>Product</th>
            <th>Business</th>
            <th>Qty</th>
            <th>Price (₦)</th>
            <th>Subtotal (₦)</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($item = $items->fetch_assoc()): ?>
            <?php
                $product_info = getProductMeta($conn, $item['product_id']);
                $product_name = $product_info['name'] ?? 'Unknown Product';
                $original_price = (float)($product_info['price'] ?? $item['price']);
                $discount_percent = (int)($product_info['discount_percent'] ?? 0);

                // Calculate discounted price only
                $discounted_price = $discount_percent > 0
                    ? round($original_price * (1 - $discount_percent / 100), 2)
                    : $original_price;

                $subtotal = $discounted_price * $item['quantity'];
                $total += $subtotal;
            ?>
            <tr>
                <td><?= htmlspecialchars($product_name) ?></td>
                <td><?= htmlspecialchars($item['business_name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>₦<?= number_format($discounted_price) ?></td>
                <td>₦<?= number_format($subtotal) ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<h4>Total Paid: ₦<?= number_format($total) ?></h4>

<div class="footer">
    <p>Thank you for shopping with us. Powered by F&V AgroServices.</p>
</div>

</body>
</html>

<?php
$html = ob_get_clean();

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output download
$dompdf->stream("Invoice_{$ref}.pdf", ["Attachment" => true]);

// 🔽 Helper function
function getProductMeta($conn, $product_id) {
    $stmt = $conn->prepare("SELECT name, price, discount_percent FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res->fetch_assoc();
    $stmt->close();
    return $data ?? [];
}
?>
