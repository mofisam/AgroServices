<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "seller") {
    header("Location: login");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch business details
$stmt = $conn->prepare("SELECT business_name, business_address FROM business_accounts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($business_name, $business_address);
$stmt->fetch();
$stmt->close();

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = trim($_POST["business_name"]);
    $new_address = trim($_POST["business_address"]);

    $update_stmt = $conn->prepare("UPDATE business_accounts SET business_name = ?, business_address = ? WHERE user_id = ?");
    $update_stmt->bind_param("ssi", $new_name, $new_address, $user_id);
    
    if ($update_stmt->execute()) {
        echo "✅ Business details updated successfully!";
    } else {
        echo "❌ Error updating details: " . $conn->error;
    }
    $update_stmt->close();
}
?>

<h2>Update Business Information</h2>
<form method="post">
    <input type="text" name="business_name" value="<?php echo $business_name; ?>" required>
    <input type="text" name="business_address" value="<?php echo $business_address; ?>" required>
    <button type="submit">Update</button>
</form>
