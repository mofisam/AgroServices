<?php
include '../../config/db.php';

$name = trim($_POST['name']);
$desc = trim($_POST['description']);
$imagePath = null;

// === 1. Handle Image Upload ===
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $uploadDir = "../../uploads/category_images/";
    $imageName = time() . "_" . basename($_FILES["image"]["name"]);
    $imagePath = $uploadDir . $imageName;

    // Optional: validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($_FILES['image']['type'], $allowedTypes)) {
        echo "❌ Invalid image type!";
        exit();
    }

    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
        echo "❌ Failed to upload image!";
        exit();
    }
}

// === 2. Insert Category Record ===
$stmt = $conn->prepare("INSERT INTO product_categories (name, description, image) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $desc, $imagePath);

if ($stmt->execute()) {
    echo "✅ Category added successfully";
} else {
    echo "❌ Failed to add category: " . $stmt->error;
}
