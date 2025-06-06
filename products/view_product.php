<?php
session_start();
include '../config/db.php';
include '../includes/header.php';

$product_id = $_GET['id'] ?? 0;

// Fetch product with additional details including category
$stmt = $conn->prepare("
    SELECT p.*, 
           ba.business_name,
           ba.business_address,
           u.profile_picture as seller_image,
           pc.name as category_name,
           COUNT(r.id) as review_count,
           ROUND(AVG(r.rating),1) as avg_rating
    FROM products p 
    JOIN users u ON u.id = p.seller_id 
    JOIN business_accounts ba ON ba.user_id = u.id
    LEFT JOIN product_categories pc ON pc.id = p.category_id
    LEFT JOIN product_reviews r ON r.product_id = p.id
    WHERE p.id = ? AND p.status = 'active'
    GROUP BY p.id
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Product not found.</div></div>";
    include '../includes/footer.php';
    exit;
}

// Add to Cart functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'])) {
    $qty = max(1, (int)$_POST['quantity']);
    $_SESSION['cart'][$product_id] = ['quantity' => $qty];
    $success = true;
}

// Fetch reviews with user details
$reviews = $conn->prepare("
    SELECT r.*, 
           u.first_name, 
           u.last_name, 
           u.profile_picture,
           DATE_FORMAT(r.created_at, '%M %d, %Y') as review_date
    FROM product_reviews r 
    JOIN users u ON u.id = r.user_id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
    LIMIT 10
");
$reviews->bind_param("i", $product_id);
$reviews->execute();
$review_result = $reviews->get_result();

// Calculate rating distribution
$rating_distribution = [];
for ($i = 1; $i <= 5; $i++) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM product_reviews WHERE product_id = ? AND rating = ?");
    $stmt->bind_param("ii", $product_id, $i);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $rating_distribution[$i] = $count;
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> | AgroServices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .product-image {
            width: 100%;
            height: 400px;
            object-fit: contain;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .thumbnail-container {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .thumbnail:hover, .thumbnail.active {
            border-color: #0d6efd;
        }
        
        .price-tag {
            font-size: 1.8rem;
            font-weight: 700;
            color: #198754;
        }
        
        .discount-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: 600;
        }
        
        .seller-card {
            border-radius: 8px;
            border: 1px solid #eee;
            transition: all 0.3s ease;
        }
        
        .seller-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .seller-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .rating-stars {
            color: #ffc107;
        }
        
        .rating-progress {
            height: 8px;
            border-radius: 4px;
        }
        
        .review-card {
            border-radius: 8px;
            border: 1px solid #eee;
            transition: all 0.3s ease;
        }
        
        .review-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .reviewer-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .quantity-input {
            width: 80px;
            text-align: center;
        }
        
        .product-meta {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .product-category {
            display: inline-block;
            background: #e9ecef;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
        }
        
        @media (max-width: 768px) {
            .product-image {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <!-- Product Details Section -->
        <div class="row g-4">
            <!-- Product Images -->
            <div class="col-lg-6">
                <div class="position-relative">
                    <?php if ($product['discount_percent'] > 0): ?>
                        <span class="discount-badge"><?= $product['discount_percent'] ?>% OFF</span>
                    <?php endif; ?>
                    <img src="<?= htmlspecialchars($product['image'] ?? '../assets/images/product-placeholder.jpg') ?>" 
                         class="product-image shadow-sm" 
                         id="mainImage"
                         alt="<?= htmlspecialchars($product['name']) ?>">
                </div>
                <div class="thumbnail-container">
                    <!-- You can add multiple thumbnails here if you have more images -->
                    <img src="<?= htmlspecialchars($product['image'] ?? '../assets/images/product-placeholder.jpg') ?>" 
                         class="thumbnail active" 
                         onclick="changeMainImage(this)"
                         alt="Thumbnail 1">
                    <!-- Add more thumbnails as needed -->
                </div>
            </div>
            
            <!-- Product Info -->
            <div class="col-lg-6">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h1 class="mb-2"><?= htmlspecialchars($product['name']) ?></h1>
                        <div class="d-flex align-items-center mb-3">
                            <?php if ($product['avg_rating']): ?>
                                <div class="rating-stars me-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi <?= $i <= round($product['avg_rating']) ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-muted"><?= $product['avg_rating'] ?> (<?= $product['review_count'] ?> reviews)</span>
                            <?php else: ?>
                                <span class="text-muted">No reviews yet</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($product['stock'] > 0): ?>
                        <span class="badge bg-success">In Stock (<?= $product['stock'] ?> available)</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Out of Stock</span>
                    <?php endif; ?>
                </div>
                
                <!-- Product Meta Information -->
                <div class="product-meta mb-4">
                    <span class="me-3">Category: <span class="product-category"><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></span></span>
                    <span>SKU: <?= htmlspecialchars($product['sku'] ?? 'N/A') ?></span>
                </div>
                
                <div class="mb-4">
                    <?php if ($product['discount_percent'] > 0): ?>
                        <div class="d-flex align-items-center">
                            <span class="price-tag me-3">₦<?= number_format($product['price'] * (1 - $product['discount_percent']/100), 2) ?></span>
                            <span class="text-decoration-line-through text-muted">₦<?= number_format($product['price'], 2) ?></span>
                            <span class="badge bg-danger ms-2">Save <?= $product['discount_percent'] ?>%</span>
                        </div>
                    <?php else: ?>
                        <span class="price-tag">₦<?= number_format($product['price'], 2) ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="mb-4">
                    <h5 class="mb-3">Description</h5>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Added to cart successfully!
                        <a href="../cart/index" class="btn btn-sm btn-success ms-3">View Cart</a>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Quantity</label>
                            <div class="input-group">
                                <button class="btn btn-outline-secondary" type="button" onclick="decrementQuantity()">-</button>
                                <input type="number" name="quantity" id="quantity" class="form-control quantity-input" value="1" min="1" max="<?= $product['stock'] ?>" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="incrementQuantity()">+</button>
                            </div>
                        </div>
                        <div class="col-md-8 d-flex align-items-end">
                            <?php if ($product['stock'] > 0): ?>
                                <button type="submit" class="btn btn-primary w-100 py-2">
                                    <i class="bi bi-cart-plus me-2"></i> Add to Cart
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-secondary w-100 py-2" disabled>
                                    Out of Stock
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
                
                <!-- Seller Information -->
                <div class="card seller-card p-3 mb-4">
                    <div class="d-flex align-items-center">
                        <img src="<?= $product['seller_image'] ? '../uploads/profile_pics/'.$product['seller_image'] : '../assets/images/default-user.png' ?>" 
                             class="seller-image me-3" 
                             alt="Seller Image">
                        <div>
                            <h6 class="mb-1">Sold by <?= htmlspecialchars($product['business_name']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($product['business_address']) ?></small>
                        </div>
                    </div>
                    <div class="mt-3 d-flex gap-2">
                        <a href="ask_seller?id=<?= $product_id ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-chat-left-text me-1"></i> Message Seller
                        </a>
                        <a href="storefront?business=<?= htmlspecialchars($product['business_name']) ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-shop me-1"></i> View Store
                        </a>
                    </div>
                </div>
                
                <!-- Product Highlights -->
                <div class="mb-4">
                    <h6 class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Quality Assurance</h6>
                    <h6 class="mb-2"><i class="bi bi-truck text-primary me-2"></i> Fast Delivery</h6>
                    <h6><i class="bi bi-arrow-repeat text-info me-2"></i> Easy Returns</h6>
                </div>
            </div>
        </div>
        
        <!-- Reviews Section -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h4 class="mb-0">Customer Reviews</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($product['avg_rating']): ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center mb-4">
                                        <h1 class="display-4"><?= $product['avg_rating'] ?></h1>
                                        <div class="rating-stars mb-2">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi <?= $i <= round($product['avg_rating']) ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="text-muted">Based on <?= $product['review_count'] ?> reviews</p>
                                    </div>
                                    
                                    <div class="rating-distribution">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <div class="d-flex align-items-center mb-2">
                                                <small class="text-muted me-2"><?= $i ?> <i class="bi bi-star-fill text-warning"></i></small>
                                                <div class="progress flex-grow-1" style="height: 8px;">
                                                    <div class="progress-bar bg-warning" 
                                                         role="progressbar" 
                                                         style="width: <?= $product['review_count'] > 0 ? ($rating_distribution[$i] / $product['review_count'] * 100) : 0 ?>%" 
                                                         aria-valuenow="<?= $rating_distribution[$i] ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="<?= $product['review_count'] ?>">
                                                    </div>
                                                </div>
                                                <small class="text-muted ms-2"><?= $rating_distribution[$i] ?></small>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <?php if ($review_result->num_rows > 0): ?>
                                        <?php while ($review = $review_result->fetch_assoc()): ?>
                                            <div class="review-card p-3 mb-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    <img src="<?= $review['profile_picture'] ? '../uploads/profile_pics/'.$review['profile_picture'] : '../assets/images/default-user.png' ?>" 
                                                         class="reviewer-image me-3" 
                                                         alt="Reviewer Image">
                                                    <div>
                                                        <h6 class="mb-0"><?= htmlspecialchars($review['first_name'] . ' ' . $review['last_name']) ?></h6>
                                                        <div class="rating-stars">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <i class="bi <?= $i <= $review['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                                            <?php endfor; ?>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted ms-auto"><?= $review['review_date'] ?></small>
                                                </div>
                                                <p class="mb-0"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                                            </div>
                                        <?php endwhile; ?>
                                        
                                        <div class="text-center mt-4">
                                            <a href="product_reviews?id=<?= $product_id ?>" class="btn btn-outline-primary">
                                                <i class="bi bi-chat-square-text me-1"></i> View All Reviews
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="bi bi-chat-left-text display-5 text-muted mb-3"></i>
                                            <h5>No Reviews Yet</h5>
                                            <p class="text-muted">Be the first to review this product</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="bi bi-chat-left-text display-5 text-muted mb-3"></i>
                                <h5>No Reviews Yet</h5>
                                <p class="text-muted">Be the first to review this product</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Products (You can implement this later) -->
        <div class="row mt-5">
            <div class="col-12">
                <h4 class="mb-4">You May Also Like</h4>
                <!-- Implement related products carousel here -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Change main product image when thumbnail is clicked
        function changeMainImage(thumbnail) {
            document.getElementById('mainImage').src = thumbnail.src;
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            thumbnail.classList.add('active');
        }
        
        // Quantity adjustment functions
        function incrementQuantity() {
            const input = document.getElementById('quantity');
            const max = parseInt(input.max) || 999;
            if (parseInt(input.value) < max) {
                input.value = parseInt(input.value) + 1;
            }
        }
        
        function decrementQuantity() {
            const input = document.getElementById('quantity');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }
    </script>
</body>
</html>

<?php include '../includes/footer.php'; ?>