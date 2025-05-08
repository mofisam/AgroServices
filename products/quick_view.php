<?php
include '../config/db.php';
session_start();

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT p.*, c.name AS category FROM products p 
                        LEFT JOIN product_categories c ON p.category_id = c.id
                        WHERE p.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
?>

<div class="row">
    <div class="col-md-5">
        <img src="<?= $p['image'] ?>" class="img-fluid" style="border:1px solid #ddd;">
    </div>
    <div class="col-md-7">
        <h4><?= htmlspecialchars($p['name']) ?></h4>
        <p><strong>Price:</strong> ₦<?= number_format($p['price'], 2) ?></p>
        <p><strong>Stock:</strong> <?= $p['stock'] ?> pcs</p>
        <?php if ($p['discount_percent']): ?>
            <p><strong>Discount:</strong> <?= $p['discount_percent'] ?>%</p>
        <?php endif; ?>
        <p><strong>Category:</strong> <?= $p['category'] ?></p>
        <p><?= nl2br($p['description']) ?></p>

        <div class="mt-3">
            <button class="btn btn-success" onclick="addToCart(<?= $p['id'] ?>)">🛒 Add to Cart</button>
            <button class="btn btn-outline-danger" onclick="addToWishlist(<?= $p['id'] ?>)">💖 Wishlist</button>
        </div>
    </div>
</div>

<script>
function addToCart(productId) {
    $.post('../cart/add_to_cart.php', { product_id: productId, quantity: 1 }, function (res) {
        alert("Added to cart ✅");
    }).fail(function () {
        alert("Failed to add to cart.");
    });
}

function addToWishlist(productId) {
    $.post('wishlist_add.php', { product_id: productId }, function (res) {
        alert("Added to wishlist 💖");
    }).fail(function () {
        alert("Failed to add to wishlist.");
    });
}
</script>
