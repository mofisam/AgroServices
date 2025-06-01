<?php
session_start();
include '../../config/db.php';

// Fetch registration fee
$fee_stmt = $conn->prepare("SELECT registration_fee FROM settings LIMIT 1");
$fee_stmt->execute();
$fee_stmt->bind_result($registration_fee);
$fee_stmt->fetch();
$fee_stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // User Information
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $state = trim($_POST["state"]);
    $gender = trim($_POST["gender"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    // Business Information
    $business_name = trim($_POST["business_name"]);
    $business_address = trim($_POST["business_address"]);

    if ($password !== $confirm_password) {
        die("❌ Passwords do not match!");
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Start transaction
    $conn->begin_transaction();
    try {
        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, state, sex, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, 'seller')");
        $stmt->bind_param("sssssss", $first_name, $last_name, $email, $phone, $state, $gender, $hashed_password);
        $stmt->execute();
        $user_id = $stmt->insert_id;
        $stmt->close();
    
        // Insert into business_accounts table
        $stmt = $conn->prepare("INSERT INTO business_accounts (user_id, business_name, business_address, payment_status) VALUES (?, ?, ?, 'pending')");
        $stmt->bind_param("iss", $user_id, $business_name, $business_address);
        $stmt->execute();
        $stmt->close();
    
        // Commit transaction
        $conn->commit();

        // Redirect to payment page
        header("Location: payment.php?user_id=" . $user_id);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "❌ Registration failed: " . $e->getMessage();
    }    
}
?>

<h2>Register as a Seller</h2>
<p>Registration Fee: ₦<?php echo number_format($registration_fee, 2); ?></p>

<form method="post">
    <!-- User Details -->
    <input type="text" name="first_name" placeholder="First Name" required>
    <input type="text" name="last_name" placeholder="Last Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="tel" name="phone" placeholder="Phone Number" required>

    <label for="gender">Gender:</label>
    <select name="gender" required>
        <option value="">-- Select Gender --</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
    </select>

    <input type="text" name="state" placeholder="State" required>

    <!-- Business Details -->
    <input type="text" name="business_name" placeholder="Business Name" required>
    <input type="text" name="business_address" placeholder="Business Address" required>

    <!-- Password -->
    <input type="password" name="password" placeholder="Password" required>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
    
    <button type="submit">Proceed to Payment</button>
</form>
