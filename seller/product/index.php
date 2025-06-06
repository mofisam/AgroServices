<?php
include '../../config/db.php';
session_start();

// 🔒 Ensure only sellers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login");
    exit();
}

$seller_id = $_SESSION['user_id'];
$products = $conn->prepare("SELECT p.*, c.name as category FROM products p 
                            LEFT JOIN product_categories c ON p.category_id = c.id 
                            WHERE seller_id = ? ORDER BY created_at DESC");
$products->bind_param("i", $seller_id);
$products->execute();
$result = $products->get_result();
include '../../includes/header.php';
?>

<div class="container py-4">
    <!-- Page Header with Add Product Button -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Product Management</h2>
            <p class="text-muted mb-0">Manage your product listings and inventory</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-circle me-2"></i> Add Product
        </button>
    </div>

    <!-- Product Stats Cards -->
    <div class="row g-3 mb-4">
        <?php 
        $total_products = $result->num_rows;
        $active_products = $conn->query("SELECT COUNT(*) FROM products WHERE seller_id = $seller_id AND status = 'active'")->fetch_row()[0];
        $low_stock = $conn->query("SELECT COUNT(*) FROM products WHERE seller_id = $seller_id AND stock < 10 AND stock > 0")->fetch_row()[0];
        $out_of_stock = $conn->query("SELECT COUNT(*) FROM products WHERE seller_id = $seller_id AND stock = 0")->fetch_row()[0];
        ?>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Products</h6>
                            <h3 class="mb-0"><?= $total_products ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-box-seam text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Active Products</h6>
                            <h3 class="mb-0"><?= $active_products ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-check-circle text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Low Stock</h6>
                            <h3 class="mb-0"><?= $low_stock ?></h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-exclamation-triangle text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Out of Stock</h6>
                            <h3 class="mb-0"><?= $out_of_stock ?></h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                            <i class="bi bi-x-circle text-danger fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="../<?= $row['image'] ?>" width="60" height="60" class="rounded me-3" style="object-fit: cover;">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($row['name']) ?></h6>
                                            <small class="text-muted"><?= $row['discount_percent'] > 0 ? $row['discount_percent'].'% off' : 'No discount' ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>₦<?= number_format($row['price'] - ($row['price'] * $row['discount_percent'] / 100) , 2) ?></strong>
                                        <?php if($row['discount_percent'] > 0): ?>
                                            <div class="text-danger"><small><s>₦<?= number_format($row['price'] , 2) ?></s></small></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="<?= $row['stock'] == 0 ? 'text-danger' : ($row['stock'] < 10 ? 'text-warning' : 'text-success') ?>">
                                        <?= $row['stock'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $row['status'] === 'active' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($row['category'] ?? 'Uncategorized') ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary edit-btn" data-id="<?= $row['id'] ?>" data-bs-toggle="modal" data-bs-target="#editModal" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info stock-btn" data-id="<?= $row['id'] ?>" data-bs-toggle="modal" data-bs-target="#stockModal" title="Add Stock">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                        <?php if ($row['status'] === 'active'): ?>
                                            <a href="product_toggle_status?id=<?= $row['id'] ?>&status=inactive" class="btn btn-sm btn-outline-secondary" title="Deactivate">
                                                <i class="bi bi-pause"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="product_toggle_status?id=<?= $row['id'] ?>&status=active" class="btn btn-sm btn-outline-success" title="Activate">
                                                <i class="bi bi-play"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="product_delete?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this product?')" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if($total_products == 0): ?>
                <div class="text-center py-5">
                    <i class="bi bi-box-seam display-5 text-muted mb-3"></i>
                    <h5>No products found</h5>
                    <p class="text-muted">You haven't added any products yet</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="bi bi-plus-circle me-2"></i> Add Your First Product
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modals -->
<?php include 'product_modal.php'; ?>
<?php include '../../includes/footer.php'?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function() {
    $(".edit-btn").click(function() {
        let id = $(this).data('id');
        $.get("fetch_product.php", { id }, function(data) {
            let prod = JSON.parse(data);
            $("#edit_id").val(prod.id);
            $("#edit_name").val(prod.name);
            $("#edit_price").val(prod.price);
            $("#edit_stock").val(prod.stock);
            $("#edit_discount").val(prod.discount_percent);
            $("#edit_description").val(prod.description);
            $("#edit_category").val(prod.category_id);
        });
    });
    
    $(".stock-btn").click(function () {
        const id = $(this).data("id");
        $("#stock_product_id").val(id);
    });
});
</script>