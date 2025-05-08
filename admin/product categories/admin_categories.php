<?php
include '../../config/db.php';
session_start();

// Ensure only admin users can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$categories = $conn->query("SELECT * FROM product_categories WHERE is_deleted = 0 ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <h2>🗂️ Manage Product Categories</h2>

    <button class="btn btn-primary my-3" data-bs-toggle="modal" data-bs-target="#addModal">+ Add New Category</button>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th> Image</th>
                <th>Description</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php $i = 1; while ($row = $categories->fetch_assoc()): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td>
                    <?php if ($row['image']): ?>
                        <img src="<?= $row['image'] ?>" width="50" height="50">
                    <?php endif; ?>
                </td>

                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <button class="btn btn-sm btn-warning edit-btn" data-id="<?= $row['id'] ?>" data-bs-toggle="modal" data-bs-target="#editModal">Edit</button>
                    <a href="category_delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="addCategoryForm" method="POST" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Add New Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input name="name" class="form-control mb-2" placeholder="Category Name" required>
                        <input type="file" name="image" class="form-control mb-2">
                        <textarea name="description" class="form-control" placeholder="Description (optional)"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editCategoryForm" class="modal-content" method="POST" enctype="multipart/form-data">
               
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <input name="name" id="edit_name" class="form-control mb-2" required>
                        <input type="file" name="image" class="form-control mb-2">
                        <textarea name="description" id="edit_description" class="form-control"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Update Category</button>
                    </div>
            </form>
        </div>
    </div>

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
            success: function(response) {
                alert(response);
                if (response.includes("Category added")) {
                    location.reload();
                }
            },
            error: function() {
                alert("Error occurred while adding category.");
            }
        });
    });

    // Populate edit modal form
    $(".edit-btn").click(function() {
        var id = $(this).data("id");
        $.get("fetch_category.php", { id: id }, function(data) {
            const category = JSON.parse(data);
            $("#edit_id").val(category.id);
            $("#edit_name").val(category.name);
            $("#edit_description").val(category.description);
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
            success: function(response) {
                alert(response);
                if (response.includes("updated")) {
                    location.reload();
                }
            },
            error: function() {
                alert("Error occurred while updating category.");
            }
        });
    });
});
</script>

</body>
</html>
