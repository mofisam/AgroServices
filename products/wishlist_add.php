<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "🔒 Login to use wishlist!";
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];

// Check if already in wishlist
$check = $conn->prepare("SELECT id FROM wishlists WHERE user_id=? AND product_id=?");
$check->bind_param("ii", $user_id, $product_id);
$check->execute();
$exists = $check->get_result()->fetch_assoc();

if ($exists) {
    $del = $conn->prepare("DELETE FROM wishlists WHERE user_id=? AND product_id=?");
    $del->bind_param("ii", $user_id, $product_id);
    $del->execute();
    echo "❌ Removed from wishlist";
} else {
    $add = $conn->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)");
    $add->bind_param("ii", $user_id, $product_id);
    $add->execute();
    echo "✅ Added to wishlist!";
}
