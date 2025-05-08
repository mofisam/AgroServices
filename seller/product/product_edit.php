<?php
include '../../config/db.php';
session_start();

if ($_SESSION['role'] !== 'seller') {
    exit("Access denied");
}

$seller_id = $_SESSION['user_id'];

$id = $_POST['id'];
$name = $_POST['name'];
$price = $_POST['price'];
$stock = $_POST['stock'];
$discount = $_POST['discount_percent'] ?? 0;
$desc = $_POST['description'];
$cat_id = $_POST['category_id'];

// Validate stock
$stmt = $conn->prepare("SELECT stock FROM products WHERE id = ? AND seller_id = ?");
$stmt->bind_param("ii", $id, $seller_id);
$stmt->execute();
$old = $stmt->get_result()->fetch_assoc();
$oldStock = $old['stock'];

if ($stock < 0 || $stock > $oldStock + 9999) {
    exit("Invalid stock quantity.");
}

// Get existing image
$stmt = $conn->prepare("SELECT image FROM products WHERE id = ? AND seller_id = ?");
$stmt->bind_param("ii", $id, $seller_id);
$stmt->execute();
$old = $stmt->get_result()->fetch_assoc();
$oldImage = $old['image'];

// Handle image upload
$uploadDir = "../../uploads/product_images/"; // The directory where images are stored

if (!empty($_FILES['image']['name'])) {

    // Ensure the upload directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true); // Create directory if it doesn't exist
    }

    // Generate a unique name for the image to avoid conflicts
    $imgName = time() . "_" . basename($_FILES["image"]["name"]); // Unique image name
    $uploadPath = $uploadDir . $imgName; // Full path on the server

    // Check if the image upload is successful
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $uploadPath)) {
        // If there is an old image, delete it from the directory
        if ($oldImage && file_exists($uploadDir . $oldImage)) {
            unlink($uploadDir . $oldImage); // Remove the old image
        }
        // Store the relative image path in the database
        $newImage = "../uploads/product_images/" . $imgName; // Store relative path
    } else {
        exit("Failed to move uploaded image.");
    }
} else {
    // If no new image is uploaded, retain the old image path
    $newImage = $oldImage;
}

// Update the product in the database
$stmt = $conn->prepare("UPDATE products SET name=?, price=?, stock=?, discount_percent=?, description=?, image=?, category_id=? 
                        WHERE id=? AND seller_id=?");
$stmt->bind_param("sdidssiii", $name, $price, $stock, $discount, $desc, $newImage, $cat_id, $id, $seller_id);
$stmt->execute();

// Redirect to manage products page
header("Location: manage_products.php");
exit();
?>
