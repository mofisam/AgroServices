<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);
    $state = trim($_POST["state"]);
    $sex = trim($_POST["sex"]);

    $update_pic = "";
    $profile_picture = null;

    if (isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] === 0) {
        $target_dir = "uploads/";
        $file_ext = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));

        if (in_array($file_ext, ["jpg", "jpeg", "png"])) {
            $profile_picture = uniqid() . "." . $file_ext;
            move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_dir . $profile_picture);
            $update_pic = ", profile_picture=?";
        }
    }

    $sql = "UPDATE users SET first_name=?, last_name=?, phone=?, address=?, state=?, sex=? $update_pic WHERE id=?";
    $stmt = $conn->prepare($sql);

    if ($profile_picture !== null) {
        $stmt->bind_param("sssssssi", $first_name, $last_name, $phone, $address, $state, $sex, $profile_picture, $user_id);
    } else {
        $stmt->bind_param("ssssssi", $first_name, $last_name, $phone, $address, $state, $sex, $user_id);
    }

    $message = $stmt->execute() ? "✅ Profile updated!" : "❌ Update failed: " . $stmt->error;
}

$stmt = $conn->prepare("SELECT first_name, last_name, phone, address, state, sex, profile_picture FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($first_name, $last_name, $phone, $address, $state, $sex, $profile_picture);
$stmt->fetch();
?>

<?php include 'includes/header.php'; ?>

<div class="bg-light py-5">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
          <h4 class="mb-0">Update Your Profile</h4>
        </div>
        <div class="card-body">
          <?php if ($message): ?>
            <div class="alert <?= str_starts_with($message, '✅') ? 'alert-success' : 'alert-danger' ?>"><?= $message ?></div>
          <?php endif; ?>
          <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
              <label for="first_name" class="form-label">First Name</label>
              <input type="text" class="form-control" name="first_name" value="<?= htmlspecialchars($first_name) ?>" required>
            </div>

            <div class="mb-3">
              <label for="last_name" class="form-label">Last Name</label>
              <input type="text" class="form-control" name="last_name" value="<?= htmlspecialchars($last_name) ?>" required>
            </div>

            <div class="mb-3">
              <label for="phone" class="form-label">Phone</label>
              <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($phone) ?>" required>
            </div>

            <div class="mb-3">
              <label for="address" class="form-label">Address</label>
              <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($address) ?>" required>
            </div>

            <div class="mb-3">
              <label for="state" class="form-label">State</label>
              <select name="state" class="form-select" required>
                <option value="<?= htmlspecialchars($state) ?>" selected><?= htmlspecialchars($state) ?></option>
                <?php
                $nigerian_states = ["Abia", "Adamawa", "Akwa Ibom", "Anambra", "Bauchi", "Bayelsa", "Benue", "Borno", "Cross River", "Delta",
                    "Ebonyi", "Edo", "Ekiti", "Enugu", "Gombe", "Imo", "Jigawa", "Kaduna", "Kano", "Katsina", "Kebbi",
                    "Kogi", "Kwara", "Lagos", "Nasarawa", "Niger", "Ogun", "Ondo", "Osun", "Oyo", "Plateau",
                    "Rivers", "Sokoto", "Taraba", "Yobe", "Zamfara", "FCT Abuja"];
                foreach ($nigerian_states as $state_option) {
                    if ($state_option !== $state) {
                        echo "<option value='$state_option'>$state_option</option>";
                    }
                }
                ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="sex" class="form-label">Sex</label>
              <select name="sex" class="form-select" required>
                <option value="Male" <?= $sex === "Male" ? "selected" : "" ?>>Male</option>
                <option value="Female" <?= $sex === "Female" ? "selected" : "" ?>>Female</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="profile_picture" class="form-label">Profile Picture</label><br>
              <?php if (!empty($profile_picture)): ?>
                <img src="uploads/<?= htmlspecialchars($profile_picture) ?>" class="img-thumbnail mb-2" style="max-height: 120px;">
              <?php endif; ?>
              <input class="form-control" type="file" name="profile_picture" accept="image/*">
            </div>

            <button type="submit" class="btn btn-success w-100">💾 Update Profile</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include('includes/footer.php'); ?>