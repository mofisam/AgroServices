<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized.");
}

$user_id = $_SESSION['user_id'];
$product_id = (int) ($_GET['id'] ?? 0);

$stmt = $conn->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();

header("Location: index.php");
exit;