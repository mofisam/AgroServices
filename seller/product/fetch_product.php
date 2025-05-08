<?php
include '../../config/db.php';
session_start();

$id = $_GET['id'];
$seller_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
$stmt->bind_param("ii", $id, $seller_id);
$stmt->execute();

echo json_encode($stmt->get_result()->fetch_assoc());
