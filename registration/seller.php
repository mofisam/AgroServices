<?php
session_start();
include '../config/db.php';

// Fetch registration fee
$fee_stmt = $conn->prepare("SELECT registration_fee FROM settings LIMIT 1");
$fee_stmt->execute();
$fee_result = $fee_stmt->get_result();
$registration_fee = $fee_result->fetch_assoc()['registration_fee'] ?? 0;
$fee_stmt->close();

$errors = [];
$input_values = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'state' => '',
    'gender' => '',
    'business_name' => '',
    'business_address' => ''
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize inputs
    $input_values = array_map('trim', $_POST);
    
    // Validate inputs
    if (empty($input_values['first_name'])) $errors['first_name'] = 'First name is required';
    if (empty($input_values['last_name'])) $errors['last_name'] = 'Last name is required';
    
    if (empty($input_values['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($input_values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $input_values['email']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors['email'] = 'Email already registered';
        }
        $stmt->close();
    }
    
    if (empty($input_values['phone'])) {
        $errors['phone'] = 'Phone number is required';
    } elseif (!preg_match('/^[0-9]{11}$/', $input_values['phone'])) {
        $errors['phone'] = 'Invalid phone number (11 digits required)';
    }
    
    if (empty($input_values['gender'])) $errors['gender'] = 'Gender is required';
    if (empty($input_values['state'])) $errors['state'] = 'State is required';
    if (empty($input_values['business_name'])) $errors['business_name'] = 'Business name is required';
    if (empty($input_values['business_address'])) $errors['business_address'] = 'Business address is required';
    
    if (empty($_POST['password'])) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($_POST['password']) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    } elseif ($_POST['password'] !== $_POST['confirm_password']) {
        $errors['confirm_password'] = 'Passwords do not match';
    }

    if (empty($errors)) {
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        // Start transaction
        $conn->begin_transaction();
        try {
            // Insert into users table
            $stmt = $conn->prepare("INSERT INTO users (id, first_name, last_name, email, phone, address, state, sex, password, role) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, 'seller')");
            $stmt->bind_param("ssssssss", 
                $input_values['first_name'],
                $input_values['last_name'],
                $input_values['email'],
                $input_values['phone'],
                $input_values['business_address'],
                $input_values['state'],
                $input_values['gender'],
                $hashed_password
            );
            $stmt->execute();
            $user_id = $stmt->insert_id;
            $stmt->close();
        
            // Insert into business_accounts table
            $stmt = $conn->prepare("INSERT INTO business_accounts (user_id, business_name, business_address, payment_status) VALUES (?, ?, ?, 'pending')");
            $stmt->bind_param("iss", $user_id, $input_values['business_name'], $input_values['business_address']);
            $stmt->execute();
            $stmt->close();
        
            // Commit transaction
            $conn->commit();

            // Redirect to payment page
            header("Location: payment?user_id=" . $user_id);
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $errors['general'] = "Registration failed: " . $e->getMessage();
        }    
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-header green-bg text-white py-3">
                    <h2 class="h4 mb-0"><i class="bi bi-shop-window me-2"></i> Seller Registration</h2>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
                    <?php endif; ?>
                    
                    <div class="alert alert-info mb-4">
                        <h5 class="alert-heading">Registration Fee: ₦<?= number_format($registration_fee, 2) ?></h5>
                        <p class="mb-0">Complete your registration by making the payment after filling this form.</p>
                    </div>
                    
                    <form method="post" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <!-- Personal Information Section -->
                            <div class="col-12">
                                <h5 class="mb-3 border-bottom pb-2">Personal Information</h5>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                                       id="first_name" name="first_name" 
                                       value="<?= htmlspecialchars($input_values['first_name']) ?>" required>
                                <?php if (isset($errors['first_name'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['first_name']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" 
                                       id="last_name" name="last_name" 
                                       value="<?= htmlspecialchars($input_values['last_name']) ?>" required>
                                <?php if (isset($errors['last_name'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['last_name']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                       id="email" name="email" 
                                       value="<?= htmlspecialchars($input_values['email']) ?>" required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                       id="phone" name="phone" 
                                       value="<?= htmlspecialchars($input_values['phone']) ?>" required>
                                <small class="text-muted">Format: 11 digits (e.g., 08012345678)</small>
                                <?php if (isset($errors['phone'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['phone']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="gender" class="form-label">Gender *</label>
                                <select class="form-select <?= isset($errors['gender']) ? 'is-invalid' : '' ?>" 
                                        id="gender" name="gender" required>
                                    <option value="">-- Select Gender --</option>
                                    <option value="Male" <?= $input_values['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= $input_values['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                                </select>
                                <?php if (isset($errors['gender'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['gender']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="state" class="form-label">State *</label>
                                <select class="form-select <?= isset($errors['state']) ? 'is-invalid' : '' ?>" 
                                        id="state" name="state" required>
                                    <option value="">-- Select State --</option>
                                    <?php
                                    $states = ['Abia', 'Adamawa', 'Akwa Ibom', 'Anambra', 'Bauchi', 'Bayelsa', 'Benue', 'Borno', 'Cross River', 'Delta', 'Ebonyi', 'Edo', 'Ekiti', 'Enugu', 'FCT', 'Gombe', 'Imo', 'Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Kogi', 'Kwara', 'Lagos', 'Nasarawa', 'Niger', 'Ogun', 'Ondo', 'Osun', 'Oyo', 'Plateau', 'Rivers', 'Sokoto', 'Taraba', 'Yobe', 'Zamfara'];
                                    foreach ($states as $state): ?>
                                        <option value="<?= $state ?>" <?= $input_values['state'] === $state ? 'selected' : '' ?>>
                                            <?= $state ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['state'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['state']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Business Information Section -->
                            <div class="col-12 mt-4">
                                <h5 class="mb-3 border-bottom pb-2">Business Information</h5>
                            </div>
                            
                            <div class="col-12">
                                <label for="business_name" class="form-label">Business Name *</label>
                                <input type="text" class="form-control <?= isset($errors['business_name']) ? 'is-invalid' : '' ?>" 
                                       id="business_name" name="business_name" 
                                       value="<?= htmlspecialchars($input_values['business_name']) ?>" required>
                                <?php if (isset($errors['business_name'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['business_name']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-12">
                                <label for="business_address" class="form-label">Business Address *</label>
                                <textarea class="form-control <?= isset($errors['business_address']) ? 'is-invalid' : '' ?>" 
                                          id="business_address" name="business_address" 
                                          rows="3" required><?= htmlspecialchars($input_values['business_address']) ?></textarea>
                                <?php if (isset($errors['business_address'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['business_address']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Password Section -->
                            <div class="col-12 mt-4">
                                <h5 class="mb-3 border-bottom pb-2">Account Security</h5>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                       id="password" name="password" required>
                                <small class="text-muted">Minimum 8 characters</small>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                       id="confirm_password" name="confirm_password" required>
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-12 mt-4">
                                <div class="form-check">
                                    <input class="form-check-input <?= isset($errors['terms']) ? 'is-invalid' : '' ?>" 
                                           type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="../terms.php" target="_blank">Terms of Service</a> and <a href="../privacy.php" target="_blank">Privacy Policy</a>
                                    </label>
                                    <?php if (isset($errors['terms'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['terms']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg w-100 py-3">
                                    <i class="bi bi-arrow-right-circle me-2"></i> Proceed to Payment
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p>Already have an account? <a href="../login.php">Login here</a></p>
            </div>
        </div>
    </div>
</div>

<script>
// Client-side form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthText = document.getElementById('password-strength');
    
    if (password.length === 0) {
        strengthText.textContent = '';
        return;
    }
    
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    
    const strengthLabels = ['Very Weak', 'Weak', 'Moderate', 'Strong', 'Very Strong'];
    strengthText.textContent = `Strength: ${strengthLabels[strength - 1]}`;
    strengthText.className = strength < 3 ? 'text-danger' : strength < 5 ? 'text-warning' : 'text-success';
});
</script>

<?php include '../includes/footer.php'; ?>