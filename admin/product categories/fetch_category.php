<?php
include '../config/db.php';

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM product_categories WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$cat = $stmt->get_result()->fetch_assoc();

echo json_encode($cat);

