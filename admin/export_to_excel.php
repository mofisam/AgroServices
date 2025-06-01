<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}


// 📝 **Headers for Excel Download**
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=payments_report_" . date('Y-m-d') . ".xls");

// 📌 **Query to Fetch Data**
$query = "
    SELECT 
        ba.business_name, 
        u.first_name, 
        u.last_name, 
        u.email, 
        u.phone,
        bpr.amount, 
        bpr.type, 
        bpr.reference, 
        bpr.status, 
        ba.payment_expiry,
        bpr.created_at 
    FROM business_accounts ba
    JOIN users u ON ba.user_id = u.id
    LEFT JOIN business_payment_records bpr ON ba.user_id = bpr.user_id
    WHERE ba.payment_status = 'paid'
    ORDER BY bpr.created_at DESC
";

$result = $conn->query($query);

// 📝 **Column Headers**
echo "Business Name\tOwner Name\tEmail\tPhone\tAmount\tType\tTransaction Reference\tStatus\tExpiry Date\tPayment Date\n";

// 🔄 **Iterate through the rows**
while ($row = $result->fetch_assoc()) {
    echo "{$row['business_name']}\t";
    echo "{$row['first_name']} {$row['last_name']}\t";
    echo "{$row['email']}\t";
    echo "{$row['phone']}\t";
    echo "₦" . number_format($row['amount'], 2) . "\t";
    echo ucfirst($row['type']) . "\t";
    echo "{$row['reference']}\t";
    echo ($row['status'] === 'success' ? "Paid" : "Pending") . "\t";
    echo ($row['payment_expiry'] ? date("M d, Y", strtotime($row['payment_expiry'])) : 'N/A') . "\t";
    echo date("M d, Y h:i A", strtotime($row['created_at'])) . "\n";
}
?>