<?php
// Fetch categories for dropdown
$cat_q = $conn->query("SELECT id, name FROM product_categories WHERE is_deleted = 0 ORDER BY name");
?>

<!-- Add Product Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="addProductForm" method="POST" enctype="multipart/form-data" action="product_add.php" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">➕ Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" name="name" class="form-control mb-2" placeholder="Product Name" required>
                <input type="number" name="price" step="0.01" class="form-control mb-2" placeholder="Price (₦)" required>
                <input type="number" name="stock" class="form-control mb-2" placeholder="Stock Quantity" required>
                <input type="number" name="discount_percent" step="0.01" class="form-control mb-2" placeholder="Discount (%)">
                <select name="category_id" class="form-select mb-2" required>
                    <option value="">-- Select Category --</option>
                    <?php while($c = $cat_q->fetch_assoc()): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <textarea name="description" class="form-control mb-2" placeholder="Description"></textarea>
                <input type="file" name="image" class="form-control mb-2" required>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editProductForm" method="POST" enctype="multipart/form-data" action="product_edit.php" class="modal-content">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-header">
                <h5 class="modal-title">✏️ Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" name="name" id="edit_name" class="form-control mb-2" required>
                <input type="number" name="price" id="edit_price" step="0.01" class="form-control mb-2" required>
                <input type="number" name="stock" id="edit_stock" class="form-control mb-2" required>
                <input type="number" name="discount_percent" id="edit_discount" step="0.01" class="form-control mb-2">
                <select name="category_id" id="edit_category" class="form-select mb-2" required>
                    <option value="">-- Select Category --</option>
                    <?php
                    $cat_q2 = $conn->query("SELECT id, name FROM product_categories WHERE is_deleted = 0 ORDER BY name");
                    while($c2 = $cat_q2->fetch_assoc()):
                    ?>
                        <option value="<?= $c2['id'] ?>"><?= htmlspecialchars($c2['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <textarea name="description" id="edit_description" class="form-control mb-2"></textarea>
                <input type="file" name="image" class="form-control mb-2">
            </div>
            <div class="modal-footer">
                <button class="btn btn-success">Update Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Add to Stock Modal -->
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="product_stock_add.php" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">➕ Add to Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="stock_product_id">
                <input type="number" name="stock_to_add" class="form-control" placeholder="Quantity to Add" required>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary">Add</button>
            </div>
        </form>
    </div>
</div>

