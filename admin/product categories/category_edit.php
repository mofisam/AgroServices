<?php
include '../../config/db.php';

$id = $_POST['id'];
$name = trim($_POST['name']);
$desc = trim($_POST['description']);
$imagePath = null;

// Validate name and description
if (empty($name)) {
    echo "❌ Category name is required!";
    exit();
}

if (empty($desc)) {
    echo "❌ Description is required!";
    exit();
}

// Fetch existing image first
$stmt = $conn->prepare("SELECT image FROM product_categories WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$existing = $result->fetch_assoc();
$existingImage = $existing['image'];

// Handle new image upload
if (!empty($_FILES['image']['name'])) {
    // Validate image file
    $targetDir = "../../uploads/category_images/";
    $newImageName = time() . "_" . basename($_FILES["image"]["name"]);
    $imagePath = $targetDir . $newImageName;
    $imageType = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

    // Check if file is a valid image
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageType, $allowedTypes)) {
        echo "❌ Invalid image type! Only JPG, JPEG, PNG, and GIF are allowed.";
        exit();
    }

    // Check file size (limit to 5MB)
    if ($_FILES["image"]["size"] > 5 * 1024 * 1024) { // 5MB
        echo "❌ Image size exceeds the limit of 5MB.";
        exit();
    }

    // Attempt to upload the new image
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
        // Optional: remove old image if exists
        if ($existingImage && file_exists($existingImage)) {
            unlink($existingImage);
        }
    } else {
        echo "❌ Failed to upload new image.";
        exit();
    }
} else {
    // Keep old image if no new one provided
    $imagePath = $existingImage;
}

// Update category
$stmt = $conn->prepare("UPDATE product_categories SET name=?, description=?, image=? WHERE id=?");
$stmt->bind_param("sssi", $name, $desc, $imagePath, $id);

if ($stmt->execute()) {
    echo "✅ Category updated successfully!";
} else {
    if ($stmt->errno === 1062) {
        echo "❌ Category name already exists!";
    } else {
        echo "❌ Error updating category: " . $stmt->error;
    }
}
?>
