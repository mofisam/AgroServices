<?php
session_start();
require '../../config/db.php';

// Set JSON response header
header('Content-Type: application/json');

// Verify admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

// Validate input
if (!isset($_POST['user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'User ID required']);
    exit();
}

$admin_id = $_SESSION['user_id'];
$user_id = intval($_POST['user_id']);

try {
    // Begin transaction for data integrity
    $conn->begin_transaction();

    // Option 1: Soft delete (recommended - keeps record but marks as deleted)
    $stmt = $conn->prepare("
        UPDATE admin_chats 
        SET is_deleted = 1, 
            deleted_at = NOW(), 
            deleted_by = ?
        WHERE user_id = ?
    ");
    $stmt->bind_param("ii", $admin_id, $user_id);
    $stmt->execute();

    // Option 2: Hard delete (uncomment to permanently remove)
   
    $stmt = $conn->prepare("
        DELETE FROM admin_chats 
        WHERE user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    

    // Log this action in audit table
    $action = "Cleared chat history with user $user_id";
    $audit = $conn->prepare("
        INSERT INTO admin_audit_log 
        (admin_id, action, ip_address, user_agent) 
        VALUES (?, ?, ?, ?)
    ");
    $audit->bind_param(
        "isss", 
        $admin_id, 
        $action, 
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    );
    $audit->execute();

    // Commit transaction
    $conn->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Conversation cleared successfully',
        'cleared_at' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?>