<?php
include '../config/db.php';
// Simple authentication check (you should implement proper authentication)
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header('Location: applications.php');
    exit;
}

// Function to sanitize input
function sanitizeInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

$id = intval($_POST['id']);
$status = sanitizeInput($_POST['status']);

$sql = "UPDATE farmer_applications SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $id);
$stmt->execute();
$stmt->close();

header("Location: view-application.php?id=$id");
exit;
?>