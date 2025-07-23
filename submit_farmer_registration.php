<?php
// Database configuration
include 'config/db.php';


// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input data
    $fullName = sanitizeInput($_POST['fullName']);
    $gender = sanitizeInput($_POST['gender']);
    $dob = !empty($_POST['dob']) ? date('Y-m-d', strtotime(str_replace('/', '-', $_POST['dob']))) : NULL;
    $phone = sanitizeInput($_POST['phone']);
    $email = !empty($_POST['email']) ? sanitizeInput($_POST['email']) : NULL;
    $address = sanitizeInput($_POST['address']);
    $state = sanitizeInput($_POST['state']);
    $lga = sanitizeInput($_POST['lga']);
    $nationality = sanitizeInput($_POST['nationality']);
    $womanInAgri = sanitizeInput($_POST['womanInAgri']);
    $isYouth = sanitizeInput($_POST['youth']);
    $activities = isset($_POST['activities']) ? implode(', ', $_POST['activities']) : '';
    $otherActivity = !empty($_POST['otherActivityText']) ? sanitizeInput($_POST['otherActivityText']) : NULL;
    $businessDescription = sanitizeInput($_POST['businessDescription']);
    $yearsInBusiness = sanitizeInput($_POST['yearsInBusiness']);
    $farmingType = sanitizeInput($_POST['farmingType']);
    $coopName = ($farmingType === 'Cooperative/group' && !empty($_POST['coopName'])) ? sanitizeInput($_POST['coopName']) : NULL;
    $trainingsInterested = isset($_POST['trainings']) ? implode(', ', $_POST['trainings']) : '';
    $challenges = isset($_POST['challenges']) ? implode(', ', $_POST['challenges']) : '';
    $otherChallenge = !empty($_POST['otherChallengeText']) ? sanitizeInput($_POST['otherChallengeText']) : NULL;
    $trainingBenefit = sanitizeInput($_POST['trainingBenefit']);
    $hasSmartphone = sanitizeInput($_POST['hasSmartphone']);
    $businessIdea = !empty($_POST['businessIdea']) ? sanitizeInput($_POST['businessIdea']) : NULL;
    $fundingAmount = !empty($_POST['fundingAmount']) ? intval($_POST['fundingAmount']) : NULL;
    $receivedGrant = !empty($_POST['receivedGrant']) ? sanitizeInput($_POST['receivedGrant']) : NULL;
    $grantDetails = ($receivedGrant === 'Yes' && !empty($_POST['grantDetails'])) ? sanitizeInput($_POST['grantDetails']) : NULL;
    $agreeTraining = sanitizeInput($_POST['agreeTraining']);
    $agreeInfo = sanitizeInput($_POST['agreeInfo']);
    
    // Validate required fields
    if (empty($fullName) || empty($gender) || empty($dob) || empty($phone) || empty($address) || 
        empty($state) || empty($lga) || empty($nationality) || empty($womanInAgri) || empty($isYouth) || 
        empty($activities) || empty($businessDescription) || empty($yearsInBusiness) || empty($farmingType) || 
        empty($trainingsInterested) || empty($challenges) || empty($trainingBenefit) || empty($hasSmartphone) || 
        empty($agreeTraining) || empty($agreeInfo)) {
        header('Location: farmer_registration?error=required_fields');
        exit;
    }
    
    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO farmer_applications (
        full_name, gender, dob, phone, email, address, state, lga, nationality,
        woman_in_agri, is_youth, activities, other_activity, business_description,
        years_in_business, farming_type, coop_name, trainings_interested, challenges,
        other_challenge, training_benefit, has_smartphone, business_idea, funding_amount,
        received_grant, grant_details, agree_training, agree_info
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param(
        "ssssssssssssssssssssssssssss", 
        $fullName, $gender, $dob, $phone, $email, $address, $state, $lga, $nationality,
        $womanInAgri, $isYouth, $activities, $otherActivity, $businessDescription,
        $yearsInBusiness, $farmingType, $coopName, $trainingsInterested, $challenges,
        $otherChallenge, $trainingBenefit, $hasSmartphone, $businessIdea, $fundingAmount,
        $receivedGrant, $grantDetails, $agreeTraining, $agreeInfo
    );
    
    // Execute and redirect
    if ($stmt->execute()) {
        // Send email notification
        sendNotificationEmail($fullName, $phone, $email);
        
        header('Location: farmer_registration?success=true');
    } else {
        header('Location: farmer_registration?error=database');
    }
    
    $stmt->close();
} else {
    header('Location: farmer_registration');
}

$conn->close();

// Helper function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to send email notification
function sendNotificationEmail($name, $phone, $email) {
    $to = "your_email@example.com"; // Change to your email
    $subject = "New Farmer Application Received";
    $message = "
    <html>
    <head>
        <title>New Farmer Application</title>
    </head>
    <body>
        <h2>New Farmer Application Submitted</h2>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Phone:</strong> $phone</p>
        <p><strong>Email:</strong> " . ($email ? $email : 'Not provided') . "</p>
        <p>Please log in to the admin panel to review this application.</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@fandvagroservices.com.ng" . "\r\n";
    
    mail($to, $subject, $message, $headers);
}