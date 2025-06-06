<?php
include 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST["token"];
    $new_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);

    // Check if token is valid and not expired
    $stmt = $conn->prepare("SELECT email FROM users WHERE reset_token=? AND reset_token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($email);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        // Update password and remove token
        $update_stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, reset_token_expiry=NULL WHERE email=?");
        $update_stmt->bind_param("ss", $new_password, $email);
        $update_stmt->execute();

        echo "✅ Password reset successful! <a href='login'>Login Now</a>";
    } else {
        echo "❌ Invalid or expired token!";
    }
}
?>

<form method="post">
    <input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">
    <input type="password" name="new_password" placeholder="Enter new password" required>
    <button type="submit">Reset Password</button>
</form>
