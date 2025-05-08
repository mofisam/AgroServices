<?php
session_start();
include '../config/db.php';

if ($_SESSION["role"] !== "admin") {
    die("Unauthorized access!");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST["id"];
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $email = $_POST["email"];
    $role = $_POST["role"];
    $admin_id = $_SESSION["admin_id"]; // Assuming admin is logged in

    // Prevent changing another admin's role
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user["role"] == "admin" && $_SESSION["role"] != "superadmin") {
        die("You cannot edit another admin!");
    }

    // Fetch old user data before updating
    $stmt = $conn->prepare("SELECT first_name, last_name, email, role FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $old_user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Update user details
    $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, role=? WHERE id=?");
    $stmt->bind_param("ssssi", $first_name, $last_name, $email, $role, $user_id);
    
    if ($stmt->execute()) {
        // Log the changes
        $changes = [];
        if ($old_user["first_name"] != $first_name) $changes[] = "First Name: {$old_user['first_name']} → $first_name";
        if ($old_user["last_name"] != $last_name) $changes[] = "Last Name: {$old_user['last_name']} → $last_name";
        if ($old_user["email"] != $email) $changes[] = "Email: {$old_user['email']} → $email";
        if ($old_user["role"] != $role) $changes[] = "Role: {$old_user['role']} → $role";

        if (!empty($changes)) {
            $log_action = "Updated user ID $user_id (" . implode(", ", $changes) . ")";
            $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, target_user_id) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $admin_id, $log_action, $user_id);
            $stmt->execute();
        }

        echo "User updated successfully!";
    } else {
        echo "Error updating user.";
    }
    $stmt->close();
}
?>
