<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    header("Location: ../products");
    exit();
}

// Get product and seller info
$stmt = $conn->prepare("
    SELECT 
        p.id, 
        p.name, 
        p.image,
        p.description,
        u.id AS seller_id, 
        u.profile_picture,
        ba.business_name
    FROM products p
    JOIN users u ON u.id = p.seller_id
    JOIN business_accounts ba ON ba.user_id = u.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: ../products");
    exit();
}

$success = $error = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');

    if (empty($message)) {
        $error = "Please enter your message.";
    } else {
        $stmt = $conn->prepare("INSERT INTO product_inquiries 
            (product_id, seller_id, user_id, message, sender_role) 
            VALUES (?, ?, ?, ?, 'buyer')");
        $stmt->bind_param("iiis", $product['id'], $product['seller_id'], $user_id, $message);
        
        if ($stmt->execute()) {
            $success = "Your message has been sent successfully!";
            $_POST = []; // Clear form
        } else {
            $error = "Failed to send message. Please try again.";
        }
    }
}
?>

<?php include '../includes/header.php' ?>

<body class="bg-gray-100">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-lg overflow-hidden mb-5">
                <!-- Product Header Section -->
                <div class="card-header bg-gradient-success text-white py-4">
                    <div class="d-flex align-items-center">
                        <?php if (!empty($product['profile_picture'])): ?>
                            <img src="<?= htmlspecialchars($product['profile_picture']) ?>" 
                                 class="rounded-circle me-3 shadow-sm" 
                                 width="60" height="60" 
                                 alt="<?= htmlspecialchars($product['business_name']) ?> profile">
                        <?php endif; ?>
                        <div>
                            <h1 class="h4 mb-0">Contact Seller</h1>
                            <p class="small mb-0 opacity-75">About: <?= htmlspecialchars($product['name']) ?></p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <!-- Product Image Section -->
                    <?php if (!empty($product['image_url'])): ?>
                    <div class="product-image-container bg-light text-center p-4">
                        <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                             class="img-fluid rounded-3 shadow-sm" 
                             style="max-height: 220px; object-fit: contain;" 
                             alt="<?= htmlspecialchars($product['name']) ?>">
                    </div>
                    <?php endif; ?>

                    <!-- Messages Section -->
                    <div class="p-4">
                        <?php if ($success): ?>
                            <div class="alert alert-success d-flex align-items-center shadow-sm">
                                <i class="bi bi-check-circle-fill me-2 fs-4"></i>
                                <div><?= htmlspecialchars($success) ?></div>
                            </div>
                        <?php elseif ($error): ?>
                            <div class="alert alert-danger d-flex align-items-center shadow-sm">
                                <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                                <div><?= htmlspecialchars($error) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Message Form Section -->
                    <div class="p-4 pt-0">
                        <form method="POST" id="inquiryForm" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="message" class="form-label fw-bold text-dark mb-2">
                                    <i class="bi bi-chat-left-text me-2 text-primary"></i>Your Message
                                </label>
                                <textarea class="form-control border-2 py-3 px-3 shadow-none" 
                                          id="message" 
                                          name="message" 
                                          rows="5" 
                                          placeholder="Type your message here..."
                                          required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                                <div class="d-flex justify-content-between mt-2">
                                    <small class="form-text text-muted">
                                        <i class="bi bi-lightbulb me-1"></i> Be specific to get a faster response
                                    </small>
                                    <small id="charCounter" class="form-text text-muted">0/500 characters</small>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-4 pt-2 border-top">
                                <a href="view_product?id=<?= $product_id ?>" class="btn btn-outline-secondary rounded-pill px-4">
                                    <i class="bi bi-arrow-left me-1"></i> Back to Product
                                </a>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm">
                                    <i class="bi bi-send-fill me-1"></i> Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Seller Info Section -->
                <div class="card-footer bg-white border-top-0 pt-0">
                    <div class="seller-info p-4 rounded-3 bg-light">
                        <h3 class="h5 mb-3 d-flex align-items-center">
                            <i class="bi bi-shop me-2 text-primary"></i>About the Seller
                        </h3>
                        <div class="d-flex align-items-center mb-3">
                            <?php if (!empty($product['profile_picture'])): ?>
                                <img src="<?= htmlspecialchars($product['profile_picture']) ?>" 
                                     class="rounded-circle me-3 shadow-sm" 
                                     width="50" height="50" 
                                     alt="<?= htmlspecialchars($product['business_name']) ?> profile">
                            <?php endif; ?>
                            <div>
                                <h4 class="h6 mb-0 fw-bold"><?= htmlspecialchars($product['business_name']) ?></h4>
                                <small class="text-muted">Verified seller</small>
                            </div>
                        </div>
                        <div class="seller-meta">
                            <div class="d-flex mb-2">
                                <div class="me-4">
                                    <i class="bi bi-clock-history text-primary me-1"></i>
                                    <span class="small">24h avg response</span>
                                </div>
                                <div>
                                    <i class="bi bi-star-fill text-warning me-1"></i>
                                    <span class="small">4.8 (120 reviews)</span>
                                </div>
                            </div>
                            <?php if (!empty($product['description'])): ?>
                                <div class="mt-3">
                                    <p class="small mb-0 text-muted">
                                        <i class="bi bi-info-circle text-primary me-1"></i>
                                        <?= htmlspecialchars($product['description']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-gradient-success {
        background: linear-gradient(135deg,rgb(58, 152, 96) 0%, #14532d 100%);
    }
    .card {
        border: none;
        overflow: hidden;
    }
    .product-image-container {
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }
    .seller-info {
        border: 1px solid rgba(0,0,0,0.05);
    }
    textarea {
        resize: none;
        min-height: 150px;
        transition: all 0.3s;
        border-color: #dee2e6 !important;
    }
    textarea:focus {
        border-color: #4e73df !important;
        box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.15) !important;
    }
    .btn-primary {
        background-color: #4e73df;
        border-color: #4e73df;
    }
    .btn-primary:hover {
        background-color: #3a5bbf;
        border-color: #3a5bbf;
    }
    .rounded-lg {
        border-radius: 1rem !important;
    }
    .rounded-pill {
        border-radius: 50rem !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counter
    const messageInput = document.getElementById('message');
    const charCounter = document.getElementById('charCounter');
    
    messageInput.addEventListener('input', function() {
        const currentLength = this.value.length;
        charCounter.textContent = `${currentLength}/500 characters`;
        
        if (currentLength > 500) {
            charCounter.classList.add('text-danger');
            charCounter.classList.remove('text-muted');
        } else {
            charCounter.classList.remove('text-danger');
            charCounter.classList.add('text-muted');
        }
    });
    
    // Form validation
    const form = document.getElementById('inquiryForm');
    form.addEventListener('submit', function(e) {
        if (messageInput.value.length > 500) {
            e.preventDefault();
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger d-flex align-items-center shadow-sm mb-4';
            alert.innerHTML = `
                <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                <div>Message should be 500 characters or less.</div>
            `;
            form.prepend(alert);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
});
</script>

<?php include "../includes/footer.php" ?>
</body>
</html>