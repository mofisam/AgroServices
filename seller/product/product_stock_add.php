<?php
include '../../config/db.php';
session_start();

if ($_SESSION['role'] !== 'seller') exit("Unauthorized");

$id = $_POST['id'];
$qty = (int)$_POST['stock_to_add'];
$seller_id = $_SESSION['user_id'];

$stmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ? AND seller_id = ?");
$stmt->bind_param("iii", $qty, $id, $seller_id);
$stmt->execute();

header("Location: index.php");
exit();
