<?php
include '../../config/db.php';

$user_id = $_GET["id"];
$stmt = $conn->prepare("UPDATE users SET status = 'suspended' WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

header("Location: manage_users.php?message=User+Suspended");
exit();
?>
<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    die("Unauthorized access!");
}

$admin_id = $_SESSION["admin_id"];
$user_id = $_GET["id"];

// Get current status
$stmt = $conn->prepare("SELECT status FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$new_status = ($user["status"] == "suspended") ? "active" : "suspended";

// Update user status
$stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
$stmt->bind_param("si", $new_status, $user_id);
$stmt->execute();
$stmt->close();

// Log action
$action = ($new_status == "suspended") ? "Suspended user ID $user_id" : "Reactivated user ID $user_id";
$stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, target_user_id) VALUES (?, ?, ?)");
$stmt->bind_param("isi", $admin_id, $action, $user_id);
$stmt->execute();
$stmt->close();

header("Location: manage_users.php?message=User+Updated");
exit();
?>
