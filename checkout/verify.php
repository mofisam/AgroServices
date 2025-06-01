<?php
session_start();
include '../config/db.php';

// 🔐 Secure entry
if (!isset($_GET['reference'], $_SESSION['checkout'])) {
    die("Unauthorized access.");
}

$reference = $_GET['reference'];
$checkout = $_SESSION['checkout'];
$paystack_secret_key = 'sk_test_41008269e1c6f30a68e89226ebe8bf9628c9e3ae'; // Replace with your live key

// ✅ **Verify Paystack Transaction**
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $paystack_secret_key",
        "Content-Type: application/json"
    ]
]);
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    die("Curl error: $err");
}

$paystack_data = json_decode($response, true);

// ✅ **Validate transaction success**
if (
    !$paystack_data || !isset($paystack_data['status']) ||
    !$paystack_data['status'] || $paystack_data['data']['status'] !== 'success'
) {
    die("Payment verification failed.");
}

// ✅ **Match data**
$paid_amount = (int) $paystack_data['data']['amount'];
$email_from_paystack = $paystack_data['data']['customer']['email'];
$checkout_email = $checkout['billing']['email'];
$checkout_amount = (int) $checkout['amount'];

if ($paid_amount !== $checkout_amount || $email_from_paystack !== $checkout_email) {
    die("Payment data mismatch.");
}

// ✅ **Insert into orders table**
$billing = $checkout['billing'];
$buyer_id = $_SESSION['user_id'] ?? null;

$stmt = $conn->prepare("INSERT INTO orders (
    buyer_id, payment_reference, first_name, last_name, email, phone,
    shipping_address, state, total_amount, payment_status
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'paid')");

$stmt->bind_param(
    "isssssssi",
    $buyer_id,
    $checkout['reference'],
    $billing['first_name'],
    $billing['last_name'],
    $billing['email'],
    $billing['phone'],
    $billing['address'],
    $billing['state'],
    $checkout_amount
);

if (!$stmt->execute()) {
    die("Failed to save order.");
}
$order_id = $stmt->insert_id;
$stmt->close();

// ✅ **Insert order_items and update Seller Wallet**
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $product_id = (int) $product_id;
        $qty = is_array($item) && isset($item['quantity']) ? (int) $item['quantity'] : (int)$item;

        // Get product info
        $prod_stmt = $conn->prepare("SELECT price, discount_percent, seller_id FROM products WHERE id = ?");
        $prod_stmt->bind_param("i", $product_id);
        $prod_stmt->execute();
        $prod_result = $prod_stmt->get_result();
        $product = $prod_result->fetch_assoc();
        $prod_stmt->close();

        if (!$product) continue;

        $price = (int)$product['price'];
        $discount = (int)$product['discount_percent'];
        $seller_id = (int)$product['seller_id'];

        $final_price = $discount > 0 ? round($price * (1 - $discount / 100)) : $price;
        $subtotal = $final_price * $qty;

        // 💰 **Calculate seller earnings**
        $seller_earnings = round($subtotal * 0.97, 2); // 97% for the seller
        $platform_fee = round($subtotal * 0.03, 2);    // 3% for the platform

        // ✅ **Update or Insert Wallet Information**
        $wallet_stmt = $conn->prepare("INSERT INTO seller_wallets (seller_id, current_balance, total_earned)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            current_balance = current_balance + VALUES(current_balance),
            total_earned = total_earned + VALUES(total_earned)");
        $wallet_stmt->bind_param("idd", $seller_id, $seller_earnings, $seller_earnings);
        $wallet_stmt->execute();
        $wallet_stmt->close();

        // Get business name
        $biz_stmt = $conn->prepare("SELECT business_name FROM business_accounts WHERE user_id = ?");
        $biz_stmt->bind_param("i", $seller_id);
        $biz_stmt->execute();
        $biz_res = $biz_stmt->get_result();
        $biz_data = $biz_res->fetch_assoc();
        $biz_stmt->close();

        $business_name = $biz_data['business_name'] ?? 'N/A';

        // ✅ **Insert into order_items**
        $item_stmt = $conn->prepare("INSERT INTO order_items (
            order_id, product_id, quantity, price, discount_percent, subtotal, business_name
        ) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $item_stmt->bind_param("iiiidis", $order_id, $product_id, $qty, $price, $discount, $subtotal, $business_name);
        $item_stmt->execute();
        $item_stmt->close();
    }
}

// ✅ **Clear session data**
unset($_SESSION['cart']);
unset($_SESSION['checkout']);

// ✅ **Redirect to Success Page**
header("Location: success.php?ref=" . urlencode($reference));
exit();
?>
