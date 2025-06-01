<?php
session_start();
require 'config/db.php';

// Set JSON response header
header('Content-Type: application/json');

// Validate session and required parameters
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['user_id']) || !isset($_GET['last_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit();
}

$user_id = intval($_GET['user_id']);
$last_id = intval($_GET['last_id']);

try {
    // Prepare query to fetch only new messages
    $query = "SELECT id, message, sender, created_at 
              FROM admin_chats 
              WHERE user_id = ? AND id > ?
              ORDER BY created_at ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $last_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    $new_last_id = $last_id;

    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'id' => $row['id'],
            'message' => nl2br(htmlspecialchars($row['message'])),
            'sender' => $row['sender'],
            'created_at' => $row['created_at']
        ];
        $new_last_id = max($new_last_id, $row['id']);
    }

    // Return the messages and new last ID
    echo json_encode([
        'success' => true,
        'newMessages' => $messages,
        'last_id' => $new_last_id
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?>