<?php
session_start();

$product_id = (int) ($_POST['product_id'] ?? 0);
$qty = max(1, (int) ($_POST['quantity'] ?? 1));

if ($product_id > 0) {
    $_SESSION['cart'][$product_id] = ['quantity' => $qty];
}

header("Location: ../cart/index.php");
exit;
