<?php
session_start();
include '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    exit("Unauthorized");
}

$seller_id = $_SESSION['user_id'];
$to_user = $_POST['user_id'];
$message = trim($_POST['message'] ?? '');

if ($message && $to_user) {
    // reuse any product they talked about
    $getProduct = $conn->prepare("
        SELECT product_id 
        FROM product_inquiries 
        WHERE user_id = ? AND seller_id = ? 
        ORDER BY created_at DESC LIMIT 1
    ");
    $getProduct->bind_param("ii", $to_user, $seller_id);
    $getProduct->execute();
    $result = $getProduct->get_result()->fetch_assoc();
    $product_id = $result['product_id'] ?? null;

    if ($product_id) {
        $stmt = $conn->prepare("
    INSERT INTO product_inquiries (product_id, seller_id, user_id, message, sender_role, read_status)
    VALUES (?, ?, ?, ?, 'seller', 'unread')
    ");
    
        
        $stmt->bind_param("iiis", $product_id, $seller_id, $to_user, $message);
        $stmt->execute();
    }
}

header("Location: seller_messages.php?user=$to_user");
exit();
