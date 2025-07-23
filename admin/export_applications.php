<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login");
    exit();
}

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="farmer_applications_'.date('Y-m-d').'.xls"');

// Create query to get all data
$sql = "SELECT * FROM farmer_applications ORDER BY submission_date DESC";
$result = $conn->query($sql);

// Start Excel HTML table
echo '<table border="1">';
echo '<tr>';
echo '<th>ID</th>';
echo '<th>Full Name</th>';
echo '<th>Gender</th>';
echo '<th>Date of Birth</th>';
echo '<th>Phone</th>';
echo '<th>Email</th>';
echo '<th>Address</th>';
echo '<th>State</th>';
echo '<th>LGA</th>';
echo '<th>Nationality</th>';
echo '<th>Woman in Agriculture</th>';
echo '<th>Youth (18-35)</th>';
echo '<th>Activities</th>';
echo '<th>Other Activity</th>';
echo '<th>Business Description</th>';
echo '<th>Years in Business</th>';
echo '<th>Farming Type</th>';
echo '<th>Cooperative/Group Name</th>';
echo '<th>Trainings Interested</th>';
echo '<th>Challenges</th>';
echo '<th>Other Challenge</th>';
echo '<th>Training Benefit</th>';
echo '<th>Has Smartphone</th>';
echo '<th>Business Idea</th>';
echo '<th>Funding Amount Needed</th>';
echo '<th>Received Grant Before</th>';
echo '<th>Grant Details</th>';
echo '<th>Agree to Training</th>';
echo '<th>Agree Info Accurate</th>';
echo '<th>Status</th>';
echo '<th>Submission Date</th>';
echo '</tr>';

// Add data rows
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>'.$row['id'].'</td>';
        echo '<td>'.$row['full_name'].'</td>';
        echo '<td>'.$row['gender'].'</td>';
        echo '<td>'.$row['dob'].'</td>';
        echo '<td>'.$row['phone'].'</td>';
        echo '<td>'.$row['email'].'</td>';
        echo '<td>'.$row['address'].'</td>';
        echo '<td>'.$row['state'].'</td>';
        echo '<td>'.$row['lga'].'</td>';
        echo '<td>'.$row['nationality'].'</td>';
        echo '<td>'.$row['woman_in_agri'].'</td>';
        echo '<td>'.$row['is_youth'].'</td>';
        echo '<td>'.$row['activities'].'</td>';
        echo '<td>'.$row['other_activity'].'</td>';
        echo '<td>'.str_replace(["\r\n", "\r", "\n"], " ", $row['business_description']).'</td>';
        echo '<td>'.$row['years_in_business'].'</td>';
        echo '<td>'.$row['farming_type'].'</td>';
        echo '<td>'.$row['coop_name'].'</td>';
        echo '<td>'.$row['trainings_interested'].'</td>';
        echo '<td>'.$row['challenges'].'</td>';
        echo '<td>'.$row['other_challenge'].'</td>';
        echo '<td>'.str_replace(["\r\n", "\r", "\n"], " ", $row['training_benefit']).'</td>';
        echo '<td>'.$row['has_smartphone'].'</td>';
        echo '<td>'.str_replace(["\r\n", "\r", "\n"], " ", $row['business_idea']).'</td>';
        echo '<td>'.$row['funding_amount'].'</td>';
        echo '<td>'.$row['received_grant'].'</td>';
        echo '<td>'.$row['grant_details'].'</td>';
        echo '<td>'.$row['agree_training'].'</td>';
        echo '<td>'.$row['agree_info'].'</td>';
        echo '<td>'.$row['status'].'</td>';
        echo '<td>'.$row['submission_date'].'</td>';
        echo '</tr>';
    }
}

echo '</table>';
exit;
?>