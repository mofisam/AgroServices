<?php
session_start();
include '../config/db.php';

$product_id = $_POST['product_id'];
$qty = $_POST['qty'] ?? 1;

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] += $qty;
} else {
    $_SESSION['cart'][$product_id] = $qty;
}

echo "✅ Product added to cart!";