<?php
$paystack_secret = "your_paystack_secret_key";
$amount = 500000; // ₦5000 in kobo
$email = "user@example.com"; // Seller's email
$callback_url = "verify_payment.php";

$data = ["email" => $email, "amount" => $amount, "callback_url" => $callback_url];

$ch = curl_init("https://api.paystack.co/transaction/initialize");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $paystack_secret"]);

$response = curl_exec($ch);
$transaction = json_decode($response);
if ($transaction->status) {
    header("Location: " . $transaction->data->authorization_url);
    exit();
}
?>
