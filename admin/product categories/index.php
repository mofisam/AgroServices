<?php
include '../../config/db.php';
session_start();

// Ensure only admin users can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Get all active categories with product counts
$categories = $conn->query("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM product_categories c
    LEFT JOIN products p ON p.category_id = c.id AND p.status = 'active'
    WHERE c.is_deleted = 0 
    GROUP BY c.id
    ORDER BY c.created_at DESC
");

include '../../includes/header.php';
?>

<body class="bg-light">
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class=" shadow-sm mb-4">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h2 class="mb-0"><i class="bi bi-tags me-2"></i> Product Categories</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Category
                    </button>
                </div>
                <br>
                <div class="card-body">
                    <?php if ($categories->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Category</th>
                                        <th width="100">Image</th>
                                        <th>Description</th>
                                        <th width="120">Products</th>
                                        <th width="150">Created</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; while ($row = $categories->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($row['name']) ?></strong>
                                            </td>
                                            <td>
                                                <?php if ($row['image']): ?>
                                                    <img src="<?= htmlspecialchars($row['image']) ?>" 
                                                         class="img-thumbnail" 
                                                         style="width: 60px; height: 60px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                                         style="width: 60px; height: 60px;">
                                                        <i class="bi bi-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= !empty($row['description']) ? htmlspecialchars($row['description']) : 'No description' ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary rounded-pill">
                                                    <?= $row['product_count'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= date('M j, Y', strtotime($row['created_at'])) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-outline-primary edit-btn" 
                                                            data-id="<?= $row['id'] ?>" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editModal">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </button>
                                                    <a href="category_delete.php?id=<?= $row['id'] ?>" 
                                                       class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this category? All products in this category will become uncategorized.')">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-tags display-5 text-muted mb-3"></i>
                            <h5>No Categories Found</h5>
                            <p class="text-muted">You haven't created any product categories yet</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="bi bi-plus-circle me-1"></i> Create Your First Category
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i> Add New Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCategoryForm" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Electronics, Clothing" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div class="form-text">Recommended size: 500x500px. JPG, PNG or WEBP.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i> Edit Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCategoryForm" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <div class="form-text">Leave blank to keep current image</div>
                        <div class="mt-2" id="currentImageContainer"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i> Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Handle Add Category form submission
    $("#addCategoryForm").submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        
        $.ajax({
            url: "category_add.php",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('#addCategoryForm button[type="submit"]').html('<span class="spinner-border spinner-border-sm" role="status"></span> Saving...').prop('disabled', true);
            },
            success: function(response) {
                if (response.includes("success")) {
                    location.reload();
                } else {
                    alert(response);
                    $('#addCategoryForm button[type="submit"]').html('<i class="bi bi-save me-1"></i> Save Category').prop('disabled', false);
                }
            },
            error: function() {
                alert("Error occurred while adding category.");
                $('#addCategoryForm button[type="submit"]').html('<i class="bi bi-save me-1"></i> Save Category').prop('disabled', false);
            }
        });
    });

    // Populate edit modal form
    $(".edit-btn").click(function() {
        var id = $(this).data("id");
        $.get("fetch_category.php", { id: id })
            .done(function(data) {
                const category = JSON.parse(data);
                $("#edit_id").val(category.id);
                $("#edit_name").val(category.name);
                $("#edit_description").val(category.description);
                
                // Display current image if exists
                $("#currentImageContainer").html(
                    category.image ? 
                    `<div class="alert alert-light d-flex align-items-center">
                        <img src="${category.image}" class="me-3" style="width: 50px; height: 50px; object-fit: cover;">
                        <div>Current Image</div>
                    </div>` : 
                    '<div class="alert alert-light">No image uploaded</div>'
                );
            })
            .fail(function() {
                alert("Error loading category data");
            });
    });

    // Handle Edit Category form submission
    $("#editCategoryForm").submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        
        $.ajax({
            url: "category_edit.php",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('#editCategoryForm button[type="submit"]').html('<span class="spinner-border spinner-border-sm" role="status"></span> Updating...').prop('disabled', true);
            },
            success: function(response) {
                if (response.includes("success")) {
                    location.reload();
                } else {
                    alert(response);
                    $('#editCategoryForm button[type="submit"]').html('<i class="bi bi-check-circle me-1"></i> Update Category').prop('disabled', false);
                }
            },
            error: function() {
                alert("Error occurred while updating category.");
                $('#editCategoryForm button[type="submit"]').html('<i class="bi bi-check-circle me-1"></i> Update Category').prop('disabled', false);
            }
        });
    });
});
</script>
</body>
</html>