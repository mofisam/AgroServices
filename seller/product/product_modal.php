<?php
// Fetch categories for dropdown
$cat_q = $conn->query("SELECT id, name FROM product_categories WHERE is_deleted = 0 ORDER BY name");
?>

<!-- Add Product Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="addProductForm" method="POST" enctype="multipart/form-data" action="product_add.php" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i> Add New Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="productName" class="form-label">Product Name</label>
                            <input type="text" name="name" class="form-control" id="productName" placeholder="Enter product name" required>
                        </div>
                        
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="productPrice" class="form-label">Price (₦)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₦</span>
                                        <input type="number" name="price" step="0.01" class="form-control" id="productPrice" placeholder="0.00" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Discount</label>
                                    <div class="input-group mb-2">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" id="addDiscountTypeToggle">
                                            %
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" data-type="percent">Percentage (%)</a></li>
                                            <li><a class="dropdown-item" href="#" data-type="amount">Amount (₦)</a></li>
                                        </ul>
                                        <input type="number" name="discount_value" step="0.01" class="form-control" id="addDiscountInput" placeholder="0" min="0">
                                        <span class="input-group-text" id="addDiscountSuffix">%</span>
                                        <input type="hidden" name="discount_percent" id="addDiscountPercent" value="0">
                                        <input type="hidden" name="discount_type" id="addDiscountType" value="percent">
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted" id="addDiscountExplanation"></small>
                                        <span class="badge bg-light text-dark" id="addCalculatedDiscount"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="productStock" class="form-label">Stock Quantity</label>
                            <input type="number" name="stock" class="form-control" id="productStock" placeholder="Enter quantity" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="productCategory" class="form-label">Category</label>
                            <select name="category_id" class="form-select" id="productCategory" required>
                                <option value="">Select a category</option>
                                <?php while($c = $cat_q->fetch_assoc()): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="productDescription" class="form-label">Description</label>
                            <textarea name="description" class="form-control" id="productDescription" rows="4" placeholder="Enter product description"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="productImage" class="form-label">Product Image</label>
                            <input type="file" name="image" class="form-control" id="productImage" accept="image/*" required>
                            <div class="form-text">Recommended size: 800x800px, Max 2MB</div>
                            <div class="mt-2 border rounded p-2 text-center" id="imagePreview" style="display: none;">
                                <img id="previewImage" src="#" alt="Preview" class="img-fluid" style="max-height: 150px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Save Product
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="editProductForm" method="POST" enctype="multipart/form-data" action="product_edit.php" class="modal-content">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i> Edit Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="editName" class="form-label">Product Name</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="editPrice" class="form-label">Price (₦)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₦</span>
                                        <input type="number" name="price" id="edit_price" step="0.01" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Discount</label>
                                    <div class="input-group mb-2">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" id="editDiscountTypeToggle">
                                            %
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" data-type="percent">Percentage (%)</a></li>
                                            <li><a class="dropdown-item" href="#" data-type="amount">Amount (₦)</a></li>
                                        </ul>
                                        <input type="number" name="discount_value" step="0.01" class="form-control" id="editDiscountInput" placeholder="0" min="0">
                                        <span class="input-group-text" id="editDiscountSuffix">%</span>
                                        <input type="hidden" name="discount_percent" id="editDiscountPercent" value="0">
                                        <input type="hidden" name="discount_type" id="editDiscountType" value="percent">
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted" id="editDiscountExplanation"></small>
                                        <span class="badge bg-light text-dark" id="editCalculatedDiscount"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editStock" class="form-label">Stock Quantity</label>
                            <input type="number" name="stock" id="edit_stock" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editCategory" class="form-label">Category</label>
                            <select name="category_id" id="edit_category" class="form-select" required>
                                <option value="">Select a category</option>
                                <?php
                                $cat_q2 = $conn->query("SELECT id, name FROM product_categories WHERE is_deleted = 0 ORDER BY name");
                                while($c2 = $cat_q2->fetch_assoc()):
                                ?>
                                    <option value="<?= $c2['id'] ?>"><?= htmlspecialchars($c2['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="4"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editImage" class="form-label">Product Image</label>
                            <input type="file" name="image" class="form-control" id="editImage" accept="image/*">
                            <div class="form-text">Leave blank to keep current image</div>
                            <div class="mt-2 border rounded p-2 text-center" id="currentImagePreview">
                                <img id="currentImage" src="#" alt="Current Image" class="img-fluid" style="max-height: 150px;">
                                <div class="mt-1 text-muted">Current Image</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle me-1"></i> Update Product
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add to Stock Modal -->
<div class="modal fade" id="stockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="product_stock_add.php" class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle-dotted me-2"></i> Add Stock</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="stock_product_id">
                <div class="mb-3">
                    <label for="stockQuantity" class="form-label">Quantity to Add</label>
                    <div class="input-group">
                        <input type="number" name="stock_to_add" class="form-control" id="stockQuantity" placeholder="Enter quantity" required min="1">
                        <span class="input-group-text">units</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-info text-white">
                    <i class="bi bi-plus-lg me-1"></i> Add Stock
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Image preview for add modal
document.getElementById('productImage').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');
    
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.style.display = 'block';
            previewImage.src = e.target.result;
        }
        
        reader.readAsDataURL(this.files[0]);
    } else {
        preview.style.display = 'none';
    }
});

// Discount Toggle Functionality for Add Modal
function setupDiscountToggle(prefix) {
    const discountTypeToggle = document.getElementById(`${prefix}DiscountTypeToggle`);
    const discountInput = document.getElementById(`${prefix}DiscountInput`);
    const discountSuffix = document.getElementById(`${prefix}DiscountSuffix`);
    const discountType = document.getElementById(`${prefix}DiscountType`);
    const discountPercent = document.getElementById(`${prefix}DiscountPercent`);
    const priceInput = document.getElementById(`${prefix === 'edit' ? 'edit_price' : 'productPrice'}`);
    const discountExplanation = document.getElementById(`${prefix}DiscountExplanation`);
    const calculatedDiscount = document.getElementById(`${prefix}CalculatedDiscount`);
    
    // Set up event listeners for dropdown items
    document.querySelectorAll(`[data-type].${prefix}-discount`).forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const type = this.getAttribute('data-type');
            discountTypeToggle.textContent = type === 'percent' ? '%' : '₦';
            discountSuffix.textContent = type === 'percent' ? '%' : '';
            discountType.value = type;
            updateDiscountExplanation();
        });
    });
    
    // Update calculations when values change
    [priceInput, discountInput].forEach(input => {
        input?.addEventListener('input', updateDiscountExplanation);
    });
    
    function updateDiscountExplanation() {
        const price = parseFloat(priceInput.value) || 0;
        const discountValue = parseFloat(discountInput.value) || 0;
        
        if (discountType.value === 'percent') {
            // Calculate discounted price
            const discountAmount = price * (discountValue / 100);
            const discountedPrice = price - discountAmount;
            
            discountExplanation.textContent = `New price: ₦${discountedPrice.toFixed(2)}`;
            calculatedDiscount.textContent = `-₦${discountAmount.toFixed(2)}`;
            discountPercent.value = discountValue;
        } else {
            // Calculate percentage
            const percentage = (discountValue / price) * 100;
            const discountedPrice = price - discountValue;
            
            discountExplanation.textContent = `Discount: ${percentage.toFixed(2)}%`;
            calculatedDiscount.textContent = `New price: ₦${discountedPrice.toFixed(2)}`;
            discountPercent.value = percentage;
        }
    }
    
    return updateDiscountExplanation;
}

// Initialize discount toggles
const updateAddDiscount = setupDiscountToggle('add');
const updateEditDiscount = setupDiscountToggle('edit');

// Set current image and discount in edit modal
$(".edit-btn").click(function() {
    let id = $(this).data('id');
    $.get("fetch_product.php", { id }, function(data) {
        let prod = JSON.parse(data);
        $("#edit_id").val(prod.id);
        $("#edit_name").val(prod.name);
        $("#edit_price").val(prod.price);
        $("#edit_stock").val(prod.stock);
        $("#editDiscountInput").val(prod.discount_percent);
        $("#editDiscountPercent").val(prod.discount_percent);
        $("#edit_description").val(prod.description);
        $("#edit_category").val(prod.category_id);
        
        // Set current image preview
        if (prod.image) {
            $("#currentImage").attr("src", "../" + prod.image);
            $("#currentImagePreview").show();
        } else {
            $("#currentImagePreview").hide();
        }
        
        // Update discount display
        updateEditDiscount();
    });
});
</script>