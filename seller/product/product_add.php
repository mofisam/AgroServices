<?php
include '../../config/db.php';
session_start();

if ($_SESSION['role'] !== 'seller') exit("Access denied");

$name = $_POST['name'];
$price = $_POST['price'];
$stock = $_POST['stock'];
$discount = $_POST['discount_percent'] ?? 0;
$desc = $_POST['description'];
$cat_id = $_POST['category_id'];
$seller_id = $_SESSION['user_id'];

$imagePath = null;

if (!empty($_FILES['image']['name'])) {
    $uploadDir = "../uploads/product_images/"; // relative from the executing script
    $serverDir = "../../uploads/product_images/"; // actual server path for saving

    // Create the directory if it doesn't exist
    if (!is_dir($serverDir)) {
        mkdir($serverDir, 0755, true);
    }

    $imgName = time() . "_" . basename($_FILES["image"]["name"]);
    $uploadPath = $serverDir . $imgName;

    // Optional debug block (remove or comment out after testing)
    /*
    echo "<pre>";
    print_r($_FILES['image']);
    echo "Saving to: " . $uploadPath;
    echo "</pre>";
    exit();
    */

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $uploadPath)) {
        $imagePath = $uploadDir . $imgName; // this is what goes into the DB
    } else {
        exit("Failed to upload image.");
    }
}

$stmt = $conn->prepare("INSERT INTO products (seller_id, name, price, stock, discount_percent, description, image, category_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isdiissi", $seller_id, $name, $price, $stock, $discount, $desc, $imagePath, $cat_id);
$stmt->execute();

header("Location: manage_products.php");
exit();
?>
