<?php
include '../config/db.php';

$user_id = $_GET["id"];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<p><strong>Name:</strong> <?= htmlspecialchars($user["first_name"] . " " . $user["last_name"]) ?></p>
<p><strong>Email:</strong> <?= htmlspecialchars($user["email"]) ?></p>
<p><strong>Role:</strong> <?= htmlspecialchars(ucfirst($user["role"])) ?></p>
<p><strong>Registered:</strong> <?= $user["created_at"] ?></p>
