<?php
include '../config/db.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
$product_id = $_POST['product_id'];

$conn->query("UPDATE products SET impressions = impressions + 1 WHERE id = $product_id");

if ($user_id) {
    $stmt = $conn->prepare("INSERT INTO product_clicks (user_id, product_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
}