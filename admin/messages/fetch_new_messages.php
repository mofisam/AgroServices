<?php
session_start();
require '../../config/db.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Validate request
if (!isset($_GET['user_id']) || !isset($_GET['last_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit();
}

// Verify admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = intval($_GET['user_id']);
$last_id = intval($_GET['last_id']);

try {
    // Prepare query to fetch new messages
    $query = "SELECT id, user_id, message, sender, created_at 
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

    // Return the result
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