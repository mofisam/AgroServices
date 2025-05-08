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
           alt="<?= htmlspecialchars($p['name']) ?>" 
           class="card-img-top" style="height:200px; object-fit:cover;">
    </a>
    <?php if ($has_discount): ?>
      <span class="position-absolute top-0 start-0 badge bg-danger">
        -<?= $discount ?>%
      </span>
    <?php endif; ?>
  </div>

  <div class="card-body d-flex flex-column">
    <h6 class="card-title mb-1"><?= htmlspecialchars($p['name']) ?></h6>
    <small class="text-muted">By <?= htmlspecialchars($p['business_name'] ?? 'Business') ?></small>

    <div class="mt-2">
      <strong class="text-success">₦<?= number_format($final_price) ?></strong>
      <?php if ($has_discount): ?>
        <span class="text-muted text-decoration-line-through ms-2">₦<?= number_format($original_price) ?></span>
      <?php endif; ?>

      <?php if (!empty($p['avg_rating'])): ?>
        <br><small class="text-warning">⭐ <?= $p['avg_rating'] ?>/5</small>
      <?php endif; ?>
    </div>

    <div class="mt-auto pt-3 d-flex flex-wrap gap-1">
      <button class="btn btn-sm btn-outline-primary w-30 view-btn" data-id="<?= $p['id'] ?>">
        <i class="bi bi-eye"></i>
      </button>
      <button class="btn btn-sm btn-success w-30 add-cart" data-id="<?= $p['id'] ?>">
        <i class="bi bi-cart-plus"></i>
      </button>
      <button class="btn btn-sm btn-outline-danger w-30" onclick="addToWishlist(<?= $p['id'] ?>)">
        <i class="bi bi-heart"></i>
      </button>
    </div>
  </div>
</div>
