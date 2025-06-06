<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login");
    exit();
}

$buyer_id = $_SESSION['user_id'];
$success = $error = "";

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_item_id = (int) $_POST['order_item_id'];
    $rating = (int) $_POST['rating'];
    $comment = trim($_POST['comment']);

    // Check if already reviewed for this specific order item
    $check_stmt = $conn->prepare("SELECT id FROM product_reviews WHERE user_id = ? AND order_item_id = ?");
    $check_stmt->bind_param("ii", $buyer_id, $order_item_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $error = "You have already reviewed this purchase.";
    } else {
        // Validate ownership of this specific order item
        $own_stmt = $conn->prepare("
            SELECT oi.id, oi.product_id, p.name, o.payment_reference 
            FROM orders o 
            JOIN order_items oi ON o.id = oi.order_id 
            JOIN products p ON oi.product_id = p.id
            WHERE o.buyer_id = ? AND oi.id = ? AND o.payment_status = 'paid'
        ");
        $own_stmt->bind_param("ii", $buyer_id, $order_item_id);
        $own_stmt->execute();
        $result = $own_stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = "Invalid order item or not paid for.";
        } else {
            $order_item = $result->fetch_assoc();
            $product_id = $order_item['product_id'];
            
            $insert = $conn->prepare("INSERT INTO product_reviews 
                (user_id, product_id, order_item_id, rating, comment, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())");
            $insert->bind_param("iiiss", $buyer_id, $product_id, $order_item_id, $rating, $comment);
            
            if ($insert->execute()) {
                $success = "Review submitted successfully for order reference: " . $order_item['payment_reference'];
            } else {
                $error = "Error submitting review.";
            }
            $insert->close();
        }
        $own_stmt->close();
    }
    $check_stmt->close();
}

// Get order items eligible for review (purchased but not reviewed) with payment reference
$eligible_stmt = $conn->prepare("
    SELECT oi.id as order_item_id, p.id as product_id, p.name, p.image, 
           o.payment_reference, oi.quantity, oi.price, o.created_at as order_date
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    JOIN products p ON p.id = oi.product_id
    WHERE o.buyer_id = ? AND o.payment_status = 'paid' 
    AND oi.id NOT IN (SELECT order_item_id FROM product_reviews WHERE user_id = ?)
    ORDER BY o.created_at DESC
");
$eligible_stmt->bind_param("ii", $buyer_id, $buyer_id);
$eligible_stmt->execute();
$eligible_items = $eligible_stmt->get_result();
$eligible_stmt->close();

// Get user's past reviews with order item details
$review_stmt = $conn->prepare("
    SELECT pr.id, pr.rating, pr.comment, pr.created_at, 
           p.name, p.image, p.id as product_id,
           o.payment_reference, oi.quantity, oi.price, o.created_at as order_date
    FROM product_reviews pr
    JOIN order_items oi ON oi.id = pr.order_item_id
    JOIN products p ON p.id = pr.product_id
    JOIN orders o ON o.id = oi.order_id
    WHERE pr.user_id = ?
    ORDER BY pr.created_at DESC
");
$review_stmt->bind_param("i", $buyer_id);
$review_stmt->execute();
$past_reviews = $review_stmt->get_result();
$review_stmt->close();
?>
<?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Reviews</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .rating-input {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        
        .rating-input input {
            display: none;
        }
        
        .rating-input label {
            color: #ddd;
            font-size: 1.8rem;
            padding: 0 0.2rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .rating-input input:checked ~ label,
        .rating-input input:checked ~ label ~ label {
            color: #ffc107;
        }
        
        .rating-input label:hover,
        .rating-input label:hover ~ label {
            color: #ffc107;
            transform: scale(1.1);
        }
        
        .review-item {
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #eee;
        }
        
        .review-item:hover {
            background-color: #f8f9fa;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .empty-state {
            padding: 3rem 0;
            text-align: center;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
        
        .review-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .review-card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1.25rem 1.5rem;
        }
        
        .payment-ref {
            font-size: 0.8rem;
            color: #6c757d;
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
        }
        
        .order-meta {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .order-price {
            font-weight: 600;
            color: #333;
        }
        
        @media (max-width: 768px) {
            .rating-input label {
                font-size: 2.2rem;
            }
            
            .product-image {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Product Reviews</h2>
                <p class="text-muted mb-0">Review your purchased items</p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="row g-4">
            <!-- Review Submission Section -->
            <div class="col-lg-6">
                <div class="card review-card">
                    <div class="review-card-header">
                        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i> Write a Review</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($eligible_items->num_rows > 0): ?>
                            <form method="POST">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Select Purchase to Review</label>
                                    <select name="order_item_id" class="form-select" required>
                                        <option value="" disabled selected>Choose a purchased item...</option>
                                        <?php while ($item = $eligible_items->fetch_assoc()): ?>
                                            <option value="<?= $item['order_item_id'] ?>">
                                                <?= htmlspecialchars($item['name']) ?> 
                                                (<?= $item['quantity'] ?> × ₦<?= number_format($item['price'], 2) ?>)
                                                <span class="payment-ref">
                                                    Ordered: <?= date('M j, Y', strtotime($item['order_date'])) ?> | Ref: <?= $item['payment_reference'] ?>
                                                </span>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Your Rating</label>
                                    <div class="rating-input mb-2">
                                        <input type="radio" id="star5" name="rating" value="5" required>
                                        <label for="star5" class="bi bi-star-fill"></label>
                                        <input type="radio" id="star4" name="rating" value="4">
                                        <label for="star4" class="bi bi-star-fill"></label>
                                        <input type="radio" id="star3" name="rating" value="3">
                                        <label for="star3" class="bi bi-star-fill"></label>
                                        <input type="radio" id="star2" name="rating" value="2">
                                        <label for="star2" class="bi bi-star-fill"></label>
                                        <input type="radio" id="star1" name="rating" value="1">
                                        <label for="star1" class="bi bi-star-fill"></label>
                                    </div>
                                    <small class="text-muted">Tap stars to rate</small>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">Your Review</label>
                                    <textarea name="comment" class="form-control" rows="5" 
                                              placeholder="Share your honest thoughts about this product..." required></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2">
                                    <i class="bi bi-send me-2"></i> Submit Review
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-emoji-frown"></i>
                                </div>
                                <h4>No purchases to review</h4>
                                <p class="text-muted mb-4">You've reviewed all your purchased items</p>
                                <a href="products" class="btn btn-outline-primary">
                                    <i class="bi bi-cart me-2"></i> Browse Products
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Past Reviews Section -->
            <div class="col-lg-6">
                <div class="card review-card">
                    <div class="review-card-header">
                        <h5 class="mb-0"><i class="bi bi-chat-square-text me-2"></i> Your Reviews</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($past_reviews->num_rows > 0): ?>
                            <div class="review-list">
                                <?php while ($review = $past_reviews->fetch_assoc()): ?>
                                    <div class="review-item">
                                        <div class="d-flex gap-3 mb-3">
                                            <img src="<?= $review['image'] ? 'uploads/products/'.$review['image'] : 'assets/images/product-placeholder.jpg' ?>" 
                                                 class="product-image" alt="<?= htmlspecialchars($review['name']) ?>">
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <h6 class="mb-1">
                                                        <a href="product?id=<?= $review['product_id'] ?>" class="text-decoration-none">
                                                            <?= htmlspecialchars($review['name']) ?>
                                                        </a>
                                                    </h6>
                                                    <div class="rating-display text-warning">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="bi <?= $i <= $review['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                                <div class="order-meta mb-2">
                                                    Purchased <?= $review['quantity'] ?> × ₦<?= number_format($review['price'], 2) ?> 
                                                    on <?= date("F j, Y", strtotime($review['order_date'])) ?>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar me-1"></i>
                                                        Reviewed on <?= date("F j, Y", strtotime($review['created_at'])) ?>
                                                    </small>
                                                    <span class="payment-ref">
                                                        Order Ref: <?= $review['payment_reference'] ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-chat-left-text"></i>
                                </div>
                                <h4>No reviews yet</h4>
                                <p class="text-muted">Your reviews will appear here once submitted</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enhance rating input experience
        document.querySelectorAll('.rating-input label').forEach(label => {
            label.addEventListener('click', function() {
                const radioId = this.getAttribute('for');
                document.getElementById(radioId).checked = true;
            });
        });
    </script>
</body>
</html>

<?php include 'includes/footer.php'; ?>