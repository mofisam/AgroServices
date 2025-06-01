<?php
session_start();
include 'config/db.php';

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
    SELECT oi.id, oi.subtotal, oi.delivery_status, p.seller_id 
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE oi.id = ? AND p.seller_id = ?
    LIMIT 1
");
$check->bind_param("ii", $order_item_id, $seller_id);
$check->execute();
$result = $check->get_result();
$order_item = $result->fetch_assoc();
$check->close();

if (!$order_item) {
    http_response_code(404);
    echo "Order item not found or you do not have permission to update.";
    exit;
}

// ✅ **Handle Status Update**
if (isset($_POST['status'])) {
    $valid_statuses = ['pending', 'in-transit', 'delivered'];
    $new_status = $_POST['status'];
    
    if (!in_array($new_status, $valid_statuses)) {
        http_response_code(400);
        echo "Invalid status value.";
        exit;
    }

    // 🚀 **Begin Transaction**
    $conn->begin_transaction();
    try {
        // 🔄 **Update delivery status and timestamp**
        $update = $conn->prepare("UPDATE order_items SET delivery_status = ?, updated_at = NOW() WHERE id = ?");
        $update->bind_param("si", $new_status, $order_item_id);
        $update->execute();
        $update->close();

        // ✅ **If status is delivered, set a timer for 3 days for auto-confirmation**
        if ($new_status === 'delivered' && $order_item['delivery_status'] !== 'delivered') {
            // ➡️ **Schedule Auto-Confirmation in 3 Days**
            $timer_date = date('Y-m-d H:i:s', strtotime('+3 days'));
            $set_timer = $conn->prepare("
                INSERT INTO auto_confirmations (order_item_id, confirm_date) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE confirm_date = VALUES(confirm_date)
            ");
            $set_timer->bind_param("is", $order_item_id, $timer_date);
            $set_timer->execute();
            $set_timer->close();
        }

        $conn->commit();
        echo "✅ Delivery status updated successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo "❌ Failed to update delivery status.";
    }
    exit;
}

// ✅ **Handle Delivery Date Update**
if (isset($_POST['delivery_date'])) {
    $delivery_date = $_POST['delivery_date'];

    // ✅ **Validate date format (YYYY-MM-DD)**
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $delivery_date)) {
        http_response_code(400);
        echo "❌ Invalid date format. Use YYYY-MM-DD.";
        exit;
    }

    // 🚀 **Begin Transaction**
    $conn->begin_transaction();
    try {
        $update = $conn->prepare("UPDATE order_items SET estimated_delivery_date = ?, updated_at = NOW() WHERE id = ?");
        $update->bind_param("si", $delivery_date, $order_item_id);
        $update->execute();
        $update->close();
        
        $conn->commit();
        echo "✅ Delivery date updated successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo "❌ Failed to update delivery date.";
    }
    exit;
}

// ❗ If we get here, no valid update type was specified
http_response_code(400);
echo "Invalid request. Must specify either status or delivery_date.";
?>
