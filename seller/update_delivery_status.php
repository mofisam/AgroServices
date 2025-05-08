<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = (int) $_POST['order_id'];
    $new_status = $_POST['status'];

    // Only seller can update their own order
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
        http_response_code(403);
        echo "Unauthorized";
        exit;
    }

    $seller_id = $_SESSION['user_id'];

    // ✅ Verify this order belongs to the seller
    $check = $conn->prepare("
        SELECT o.id FROM orders o
        JOIN order_items oi ON oi.order_id = o.id
        JOIN products p ON p.id = oi.product_id
        WHERE o.id = ? AND p.seller_id = ?
        LIMIT 1
    ");
    $check->bind_param("ii", $order_id, $seller_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $update = $conn->prepare("UPDATE orders SET delivery_status = ? WHERE id = ?");
        $update->bind_param("si", $new_status, $order_id);
        $update->execute();
        echo "Delivery status updated.";
    } else {
        echo "Invalid order or permission denied.";
    }
} else {
    echo "Invalid request.";
}
