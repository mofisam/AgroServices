<?php
session_start();
header('Content-Type: application/json');

$count = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'] ?? 1;
    }
}

echo json_encode(['count' => $count]);
?>