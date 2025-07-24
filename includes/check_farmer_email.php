<?php
include '../config/db.php';

if (isset($_GET['email'])) {
    $email = $conn->real_escape_string($_GET['email']);
    $stmt = $conn->prepare("SELECT id FROM farmer_applications WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    header('Content-Type: application/json');
    echo json_encode(['exists' => $stmt->num_rows > 0]);
    
    $stmt->close();
    exit;
}

header('Content-Type: application/json');
echo json_encode(['exists' => false]);
?>