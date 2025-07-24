<?php
include '../config/db.php';

if (isset($_GET['phone'])) {
    $phone = $conn->real_escape_string($_GET['phone']);
    $stmt = $conn->prepare("SELECT id FROM farmer_applications WHERE phone = ?");
    $stmt->bind_param("s", $phone);
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