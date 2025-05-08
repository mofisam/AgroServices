<?php
include '../../config/db.php';
session_start();

if ($_SESSION['role'] !== 'seller') exit("Unauthorized");

$id = $_GET['id'];
$status = ($_GET['status'] === 'inactive') ? 'inactive' : 'active';
$seller_id = $_SESSION['user_id'];

$stmt = $conn->prepare("UPDATE products SET status = ? WHERE id = ? AND seller_id = ?");
$stmt->bind_param("sii", $status, $id, $seller_id);
$stmt->execute();

header("Location: manage_products.php");
exit();
