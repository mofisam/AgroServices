<?php
session_start();
include '../config/db.php';

if ($_SESSION["role"] !== "admin") {
    header("Location: login");
    exit();
}

$result = $conn->query("SELECT admin_logs.*, users.first_name, users.last_name FROM admin_logs 
                        JOIN users ON admin_logs.admin_id = users.id 
                        ORDER BY timestamp DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Logs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h2>Admin Activity Logs</h2>
    <table class="table table-striped">
        <tr>
            <th>Admin</th>
            <th>Action</th>
            <th>User ID</th>
            <th>Timestamp</th>
        </tr>
        <?php while ($log = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($log["first_name"] . " " . $log["last_name"]) ?></td>
                <td><?= htmlspecialchars($log["action"]) ?></td>
                <td><?= htmlspecialchars($log["target_user_id"]) ?></td>
                <td><?= $log["timestamp"] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
