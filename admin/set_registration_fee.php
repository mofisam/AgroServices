<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../includes/header.php';
$success = $error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fee = trim($_POST['fee']);

    if (!is_numeric($fee) || $fee < 0) {
        $error = "❌ Please enter a valid numeric fee.";
    } else {
        // Check if there's already a row in settings
        $check = $conn->query("SELECT id FROM settings LIMIT 1");
        if ($check->num_rows > 0) {
            $update = $conn->prepare("UPDATE settings SET registration_fee = ? WHERE id = 1");
            $update->bind_param("d", $fee);
            $update->execute();
            $update->close();
        } else {
            $insert = $conn->prepare("INSERT INTO settings (registration_fee) VALUES (?)");
            $insert->bind_param("d", $fee);
            $insert->execute();
            $insert->close();
        }

        $success = "✅ Registration fee updated to ₦" . number_format($fee, 2);
    }
}

// Fetch current registration fee
$fee = 0.00;
$result = $conn->query("SELECT registration_fee FROM settings LIMIT 1");
if ($row = $result->fetch_assoc()) {
    $fee = $row['registration_fee'];
}
?>

<div class="container py-5">
    <h2 class="mb-4">⚙️ Set Registration Fee</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="card p-4 shadow-sm bg-light" style="max-width: 500px">
        <div class="mb-3">
            <label class="form-label">Current Fee (₦)</label>
            <input type="number" name="fee" step="0.01" class="form-control" value="<?= htmlspecialchars($fee) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">💾 Update Registration Fee</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>