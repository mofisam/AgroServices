<?php
include '../../config/db.php';
session_start();

if ($_SESSION['role'] !== 'seller') exit("Access denied");

$id = $_GET['id'];
$seller_id = $_SESSION['user_id'];

$stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
$stmt->bind_param("ii", $id, $seller_id);
$stmt->execute();

header("Location: index.php");
exit();
