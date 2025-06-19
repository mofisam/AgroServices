<?php
include '../../config/db.php';

$id = $_GET['id'];

$stmt = $conn->prepare("UPDATE product_categories SET is_deleted = 1 WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: index?deleted=true");
exit();
