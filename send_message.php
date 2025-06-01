<?php
include 'config/db.php';

$user_id = intval($_POST['user_id'] ?? $_SESSION['user_id']);
$message = htmlspecialchars($_POST['message']);
$sender = htmlspecialchars($_POST['sender']);

$stmt = $conn->prepare("INSERT INTO admin_chats (user_id, message, sender) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $message, $sender);

if ($stmt->execute()) {
    echo "Message sent.";
} else {
    echo "Failed to send message.";
}
$stmt->close();
?>
