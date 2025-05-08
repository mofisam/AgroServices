<?php
session_start();
include '../config/db.php';

// Check if user is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

// Fetch all sellers
$sellers_stmt = $conn->prepare("SELECT u.id, u.first_name, u.last_name, u.email, b.business_name, b.payment_status, b.payment_expiry FROM users u JOIN business_accounts b ON u.id = b.user_id WHERE u.role = 'seller'");
$sellers_stmt->execute();
$sellers_result = $sellers_stmt->get_result();

// Fetch current registration fee
$fee_stmt = $conn->prepare("SELECT registration_fee FROM settings LIMIT 1");
$fee_stmt->execute();
$fee_stmt->bind_result($registration_fee);
$fee_stmt->fetch();
$fee_stmt->close();

// Handle fee update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["new_fee"])) {
    $new_fee = floatval($_POST["new_fee"]);
    $update_stmt = $conn->prepare("UPDATE settings SET registration_fee = ?");
    $update_stmt->bind_param("d", $new_fee);
    $update_stmt->execute();
    $update_stmt->close();
    header("Location: admin_dashboard.php");
    exit();
}
?>

<h2>Admin Dashboard</h2>

<!-- Update Registration Fee -->
<form method="post">
    <label>Update Registration Fee (₦):</label>
    <input type="number" name="new_fee" value="<?php echo $registration_fee; ?>" required>
    <button type="submit">Update Fee</button>
</form>

<h3>Sellers</h3>
<table border="1">
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Business</th>
        <th>Payment Status</th>
        <th>Expires On</th>
    </tr>
    <?php while ($seller = $sellers_result->fetch_assoc()): ?>
    <tr>
        <td><?php echo $seller["first_name"] . " " . $seller["last_name"]; ?></td>
        <td><?php echo $seller["email"]; ?></td>
        <td><?php echo $seller["business_name"]; ?></td>
        <td><?php echo $seller["payment_status"]; ?></td>
        <td><?php echo $seller["payment_expiry"]; ?></td>
    </tr>
    <?php endwhile; ?>
</table>
