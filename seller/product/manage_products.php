<?php
include '../../config/db.php';
session_start();

// 🔒 Ensure only sellers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];
$products = $conn->prepare("SELECT p.*, c.name as category FROM products p 
                            LEFT JOIN product_categories c ON p.category_id = c.id 
                            WHERE seller_id = ? ORDER BY created_at DESC");
$products->bind_param("i", $seller_id);
$products->execute();
$result = $products->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <h2>🧺 Manage Your Products</h2>
    <button class="btn btn-primary my-3" data-bs-toggle="modal" data-bs-target="#addModal">+ Add Product</button>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th><th>Image</th><th>Name</th><th>Stock</th><th>Price</th><th>Discount</th><th>Category</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $i=1; while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><img src="../<?= $row['image'] ?>" width="50" height="50"></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= $row['stock'] ?></td>
                    <td>₦<?= number_format($row['price'], 2) ?></td>
                    <td><?= $row['discount_percent'] ?>%</td>
                    <td><?= htmlspecialchars($row['category']) ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm edit-btn" data-id="<?= $row['id'] ?>" data-bs-toggle="modal" data-bs-target="#editModal">Edit</button>
                        <button class="btn btn-info btn-sm stock-btn" data-id="<?= $row['id'] ?>" data-bs-toggle="modal" data-bs-target="#stockModal">Add to Stock</button>

                        <?php if ($row['status'] === 'active'): ?>
                            <a href="product_toggle_status.php?id=<?= $row['id'] ?>&status=inactive" class="btn btn-secondary btn-sm">Deactivate</a>
                        <?php else: ?>
                            <a href="product_toggle_status.php?id=<?= $row['id'] ?>&status=active" class="btn btn-success btn-sm">Activate</a>
                        <?php endif; ?>

                        <a href="product_delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this product?')" class="btn btn-danger btn-sm">Delete</a>
                    </td>

                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Modals -->
    <?php include 'product_modal.php'; ?>

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
});
$(".stock-btn").click(function () {
    const id = $(this).data("id");
    $("#stock_product_id").val(id);
});

</script>
</body>
</html>
