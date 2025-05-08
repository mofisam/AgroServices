<?php
$servername = "localhost";
$username = "root";  // Change for production
$password = "1234";
$dbname = "agro_ecommerce";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>