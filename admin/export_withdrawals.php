<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login");
    exit();
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=withdrawal_logs.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Seller Name', 'Bank Name', 'Account Number', 'Account Name', 'Amount', 'Status', 'Request Date', 'Processed Date']);

$logs = $conn->query("
    SELECT w.id, w.amount, w.bank_name, w.account_number, w.account_name, w.status, 
           u.first_name, u.last_name, w.created_at, w.processed_at 
    FROM withdrawal_requests w 
    JOIN users u ON w.user_id = u.id 
    WHERE w.status != 'pending'
");

while ($row = $logs->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['first_name'] . ' ' . $row['last_name'],
        $row['bank_name'],
        $row['account_number'],
        $row['account_name'],
        $row['amount'],
        ucfirst($row['status']),
        $row['created_at'],
        $row['processed_at'] ?? 'N/A'
    ]);
}
fclose($output);
exit();
?>