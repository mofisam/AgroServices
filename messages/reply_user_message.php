<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') exit;

$user_id = $_SESSION['user_id'];
$seller_id = $_POST['seller_id'];
$message = trim($_POST['message'] ?? '');

if ($message) {
    $stmt = $conn->prepare("
        SELECT product_id FROM product_inquiries 
        WHERE user_id = ? AND seller_id = ? 
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->bind_param("ii", $user_id, $seller_id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $product_id = $r['product_id'] ?? null;

    if ($product_id) {
        $insert = $conn->prepare("
            INSERT INTO product_inquiries (product_id, seller_id, user_id, message, sender_role, read_status)
            VALUES (?, ?, ?, ?, 'buyer', 'unread')
        ");
        $insert->bind_param("iiis", $product_id, $seller_id, $user_id, $message);
        $insert->execute();
    }
}
