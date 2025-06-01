<?php
session_start();
include '../config/db.php';

// 🔐 **Authorization Check**
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    http_response_code(403);
    echo "Unauthorized access.";
    exit;
}

$seller_id = $_SESSION['user_id'];

// 📦 **Get Order Item ID**
$order_item_id = (int) ($_POST['order_item_id'] ?? 0);
if ($order_item_id <= 0) {
    http_response_code(400);
    echo "Invalid Order Item ID.";
    exit;
}

// 🔎 **Verify this item belongs to the seller**
$check = $conn->prepare("
    SELECT oi.id 
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE oi.id = ? AND p.seller_id = ?
    LIMIT 1
");
$check->bind_param("ii", $order_item_id, $seller_id);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    http_response_code(404);
    echo "Order item not found or you do not have permission to update.";
    exit;
}

$check->close();

// ✅ **Handle Status Update**
if (isset($_POST['status'])) {
    $valid_statuses = ['pending', 'in-transit', 'delivered'];
    $new_status = $_POST['status'];
    
    if (!in_array($new_status, $valid_statuses)) {
        http_response_code(400);
        echo "Invalid status value.";
        exit;
    }

    $update = $conn->prepare("UPDATE order_items SET delivery_status = ?, updated_at = NOW() WHERE id = ?");
    $update->bind_param("si", $new_status, $order_item_id);

    if ($update->execute()) {
        echo "✅ Delivery status updated successfully.";
    } else {
        http_response_code(500);
        echo "❌ Failed to update delivery status.";
    }
    $update->close();
    exit;
}

// ✅ **Handle Delivery Date Update**
if (isset($_POST['delivery_date'])) {
    $delivery_date = $_POST['delivery_date'];

    // Validate date format (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $delivery_date)) {
        http_response_code(400);
        echo "❌ Invalid date format. Use YYYY-MM-DD.";
        exit;
    }

    $update = $conn->prepare("UPDATE order_items SET estimated_delivery_date = ?, updated_at = NOW() WHERE id = ?");
    $update->bind_param("si", $delivery_date, $order_item_id);

    if ($update->execute()) {
        echo "✅ Delivery date updated successfully.";
    } else {
        http_response_code(500);
        echo "❌ Failed to update delivery date.";
    }
    $update->close();
    exit;
}

// ❗ If we get here, no valid update type was specified
http_response_code(400);
echo "Invalid request. Must specify either status or delivery_date.";
?>
