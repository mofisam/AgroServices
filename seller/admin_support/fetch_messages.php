<?php
include '../../config/db.php';

// Set proper headers
header('Content-Type: text/html; charset=utf-8');

// Validate and sanitize input
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($user_id <= 0) {
    die('<div class="alert alert-danger">Invalid user ID</div>');
}

// Check if we need to load messages before a specific ID (for pagination)
$before_id = isset($_GET['before_id']) ? (int)$_GET['before_id'] : 0;

try {
    // Prepare base query
    $query = "SELECT id, message, sender, created_at 
              FROM admin_chats 
              WHERE user_id = ?";
    
    // Add condition if loading older messages
    if ($before_id > 0) {
        $query .= " AND id < ?";
    }
    
    $query .= " ORDER BY created_at DESC LIMIT 50";
    
    $stmt = $conn->prepare($query);
    
    if ($before_id > 0) {
        $stmt->bind_param("ii", $user_id, $before_id);
    } else {
        $stmt->bind_param("i", $user_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Store messages to reverse order later (newest at bottom)
    $messages = [];
    $oldest_id = null;
    
    while ($msg = $result->fetch_assoc()) {
        $messages[] = $msg;
        $oldest_id = $msg['id'];
    }
    
    // Reverse the array to show newest at bottom
    $messages = array_reverse($messages);
    
    if (empty($messages)) {
        echo '<div class="text-center text-muted py-4">
                <i class="fas fa-comment-slash fa-2x mb-2"></i>
                <p>No messages yet. Start the conversation!</p>
              </div>';
        exit;
    }
    
    // Output messages
    foreach ($messages as $msg) {
        $isUser = $msg['sender'] == 'user';
        $time = date('M j, h:i A', strtotime($msg['created_at']));
        
        echo '<div class="message '.($isUser ? 'user-message' : 'admin-message').'" data-id="'.$msg['id'].'">
                '.nl2br(htmlspecialchars($msg['message'])).'
                <span class="message-time">'.$time.'</span>
              </div>';
    }
    
    // Add "Load More" button if there might be older messages
    if ($result->num_rows >= 50) {
        echo '<div class="text-center mt-3">
                <button class="btn btn-sm btn-outline-primary load-more" 
                        data-before-id="'.$oldest_id.'">
                    <i class="fas fa-history me-1"></i> Load older messages
                </button>
              </div>';
    }
    
} catch (Exception $e) {
    error_log("Fetch messages error: ".$e->getMessage());
    echo '<div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Failed to load messages. Please try again.
          </div>';
}
?>