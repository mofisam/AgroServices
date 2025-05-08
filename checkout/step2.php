<?php
session_start();
include '../config/db.php';

// ✅ Confirm POSTed billing data
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['email'], $_POST['grand_total'])) {
    header("Location: step1.php");
    exit();
}

// ✅ Collect billing details
$billing = [
    'first_name' => htmlspecialchars($_POST['first_name']),
    'last_name' => htmlspecialchars($_POST['last_name']),
    'email' => htmlspecialchars($_POST['email']),
    'phone' => htmlspecialchars($_POST['phone']),
    'address' => htmlspecialchars($_POST['address']),
    'state' => htmlspecialchars($_POST['state']),
];

$amount = (int) $_POST['grand_total'] * 100; // Paystack uses kobo
$reference = 'AGR' . time() . rand(1000, 9999);

// Save to session (or DB if you'd prefer)
$_SESSION['checkout'] = [
    'billing' => $billing,
    'amount' => $amount,
    'reference' => $reference,
];

// ✅ Paystack Keys
$paystack_secret_key = 'sk_test_41008269e1c6f30a68e89226ebe8bf9628c9e3ae'; // 🔥 replace with your real secret #citl
$paystack_public_key = 'pk_test_3d8772ab51c1407f1302d2fffc114220b0b1d9ee'; // 🔥 replace with your real public key #citl

// ✅ Initiate Transaction via CURL
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode([
        'amount' => $amount,
        'email' => $billing['email'],
        'reference' => $reference,
        'callback_url' => 'http://localhost/web/AgroServices/checkout/verify.php' //i will change it later #citl
    ]),
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $paystack_secret_key",
        "Content-Type: application/json"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo "CURL Error: $err";
    exit();
}

$res = json_decode($response);
if (!$res->status) {
    echo "Paystack Error: " . $res->message;
    exit();
}

// ✅ Redirect to payment page
header('Location: ' . $res->data->authorization_url);
exit();