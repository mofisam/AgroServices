<?php
session_start();
header('Content-Type: application/json');

// Count distinct items (keys in the cart array)
$count = 0;

if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $count = count($_SESSgit ION['cart']);
}

echo json_encode(['count' => $count]);
