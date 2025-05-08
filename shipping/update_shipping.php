<?php
session_start();
include '../config/db.php';

if ($_SESSION['role'] !== 'seller' && $_SESSION['role'] !== 'admin') {
    exit("Unauthorized access");
}

$order_id = $_POST['order_id'];
$courier = $_POST['courier_name'];
$contact = $_POST['courier_contact'];
$note = $_POST['delivery_note'];

// Mark order as "shipped" and save courier info
$stmt = $conn->prepare("UPDATE orders SET status='shipped', courier_name=?, courier_contact=?, delivery_note=? WHERE id=?");
$stmt->bind_param("sssi", $courier, $contact, $note, $order_id);

if ($stmt->execute()) {
    // Optionally notify buyer
    include '../notifications/notify.php';
    notify_buyer($order_id, "Your order has been shipped via $courier. Tracking info: $contact");
    header("Location: ../orders/seller_orders.php?shipped=true");
} else {
    echo "Failed to update shipping.";
}
