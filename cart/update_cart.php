<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantities'])) {
    foreach ($_POST['quantities'] as $product_id => $qty) {
        $product_id = (int) $product_id;
        $qty = max(1, (int)$qty);

        // Ensure cart is initialized and item is an array
        if (!isset($_SESSION['cart'][$product_id]) || !is_array($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] = ['quantity' => 0];
        }

        // Update the quantity
        $_SESSION['cart'][$product_id]['quantity'] = $qty;
    }
}

header("Location: index.php");
exit;
