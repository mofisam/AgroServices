<?php
// Redirect to appropriate dashboard based on user role
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

// Define dashboard paths based on role
$dashboards = [
    'buyer' => '../buyer/dashboard.php',
    'seller' => '../seller/dashboard.php',
    'admin' => '../admin/dashboard.php'
];

// Get the appropriate dashboard path
$role = strtolower($_SESSION['role']);
$dashboard = $dashboards[$role] ?? '../login.php';

// Redirect to dashboard
header("Location: $dashboard");
exit();
?>