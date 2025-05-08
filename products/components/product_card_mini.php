<?php if (!isset($p)) return; ?>

<?php
$original_price = (float) $p['price'];
$discount = (int) ($p['discount_percent'] ?? 0);
$has_discount = $discount > 0;
$final_price = $has_discount ? round($original_price * (1 - $discount / 100)) : $original_price;
?>

<div class="card h-100 shadow-sm">
  <div class="position-relative">
    <a href="view_product.php?id=<?= $p['id'] ?>" onclick="trackProductClick(<?= $p['id'] ?>)">
      <img src="<?= htmlspecialchars($p['image'] ?? 'assets/images/placeholder.jpg') ?>" 
           class="card-img-top" alt="<?= htmlspecialchars($p['name']) ?>" 
           style="height:140px; object-fit:cover;">

      <?php if ($has_discount): ?>
        <span class="position-absolute top-0 start-0 badge bg-danger rounded-0">
          -<?= $discount ?>%
        </span>
      <?php endif; ?>

      <div class="position-absolute bottom-0 start-0 w-100 bg-dark bg-opacity-50 text-white px-2 py-1" style="font-size: 0.75rem;">
        <?= htmlspecialchars($p['business_name'] ?? 'Seller') ?>
        <?php if (!empty($p['avg_rating'])): ?>
          <span class="float-end text-warning">⭐ <?= $p['avg_rating'] ?>/5</span>
        <?php endif; ?>
      </div>
    </a>
  </div>

  <div class="card-body p-2">
    <h6 class="card-title mb-1" style="font-size: 0.9rem;">
      <?= htmlspecialchars($p['name']) ?>
    </h6>

    <div>
      <strong class="text-success">₦<?= number_format($final_price) ?></strong>
      <?php if ($has_discount): ?>
        <span class="text-muted text-decoration-line-through ms-1" style="font-size: 0.8rem;">
          ₦<?= number_format($original_price) ?>
        </span>
      <?php endif; ?>
    </div>

    <div class="mt-2 d-flex gap-1">
      <a href="view_product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary w-100"
         onclick="trackProductClick(<?= $p['id'] ?>)">
        <i class="bi bi-eye"></i>
      </a>
      <button class="btn btn-sm btn-success w-100 add-cart" data-id="<?= $p['id'] ?>">
        <i class="bi bi-cart-plus"></i>
      </button>
      <button class="btn btn-sm btn-outline-danger w-100" onclick="addToWishlist(<?= $p['id'] ?>)">
        <i class="bi bi-heart"></i>
      </button>
    </div>
  </div>
</div>
