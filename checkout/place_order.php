<?php
session_start();
include '../config/db.php';

$buyer_id = $_SESSION['user_id'] ?? null;
$shipping = $_POST['shipping_address'] ?? '';
$total = $_POST['total'] ?? 0;
$cart = $_SESSION['cart'] ?? [];

if (!$buyer_id || !$cart || !$shipping) {
    die("Invalid order.");
}

$conn->begin_transaction();

try {
    // 1. Insert order
    $stmt = $conn->prepare("INSERT INTO orders (buyer_id, total_amount, shipping_address) VALUES (?, ?, ?)");
    $stmt->bind_param("ids", $buyer_id, $total, $shipping);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // 2. Prepare cart product IDs safely
    $cart_ids = array_map('intval', array_keys($cart)); // Force integers
    if (empty($cart_ids)) {
        throw new Exception("No valid items in cart.");
    }
    $ids_str = implode(",", $cart_ids);

    // 3. Fetch products
    $query = "SELECT * FROM products WHERE id IN ($ids_str)";
    $products_result = $conn->query($query);
    if (!$products_result) {
        throw new Exception("Product fetch failed: " . $conn->error);
    }

    // 4. Prepare insert statement for order items
    $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, seller_id, quantity, price, subtotal) 
                                 VALUES (?, ?, ?, ?, ?, ?)");

    foreach ($cart as $pid => $qty) {
        if (!is_numeric($qty) || $qty <= 0) {
            throw new Exception("Invalid quantity for product ID: $pid");
        }
    }

    while ($p = $products_result->fetch_assoc()) {
        $pid = (int)$p['id'];
        $qty = (int)$cart[$pid];
        $price = (float)$p['price'];
        $subtotal = $price * $qty;
        $seller_id = (int)$p['seller_id'];
        $stock = (int)$p['stock'];

        if ($stock < $qty) {
            throw new Exception("Not enough stock for product ID: $pid");
        }

        // Insert into order_items
        $item_stmt->bind_param("iiiiid", $order_id, $pid, $seller_id, $qty, $price, $subtotal);
        $item_stmt->execute();

        // Reduce product stock
        $update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $update_stock->bind_param("ii", $qty, $pid);
        $update_stock->execute();
    }

    // 5. All good – commit transaction
    $conn->commit();
    unset($_SESSION['cart']);
    header("Location: ../orders/buyer_orders.php?order=success");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    die("❌ Order failed: " . $e->getMessage());
}
?>
