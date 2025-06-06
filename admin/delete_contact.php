<?php
include '../config/db.php';
session_start();

// Admin Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM contact_messages WHERE id = $id");
}

header("Location: contact_messages");
exit();
?>
