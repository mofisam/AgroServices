<?php
include_once __DIR__ . '/../config/db.php';

// Get current page URL
$page_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Get visitor IP address
$ip_address = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

// Get user agent and referrer
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$referrer = $_SERVER['HTTP_REFERER'] ?? '';

// Get device type
$device_type = 'Desktop';
if (preg_match('/Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $user_agent)) {
    $device_type = 'Mobile';
} elseif (preg_match('/Tablet|iPad|Kindle|Nexus 7|Xoom|Transformer/i', $user_agent)) {
    $device_type = 'Tablet';
}

// Get location information (using free IP geolocation API)
$country = $region = $city = 'Unknown';
$ip_info = @json_decode(file_get_contents("http://ipinfo.io/{$ip_address}/json"), true);
if ($ip_info) {
    $country = $ip_info['country'] ?? 'Unknown';
    $region = $ip_info['region'] ?? 'Unknown';
    $city = $ip_info['city'] ?? 'Unknown';
}

// Check if this IP already viewed this page today
$stmt = $conn->prepare("SELECT id, view_count FROM page_views 
                        WHERE page_url = ? AND ip_address = ? 
                        AND DATE(created_at) = CURDATE() 
                        LIMIT 1");
$stmt->bind_param("ss", $page_url, $ip_address);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update view count
    $row = $result->fetch_assoc();
    $new_count = $row['view_count'] + 1;
    $update_stmt = $conn->prepare("UPDATE page_views SET view_count = ?, updated_at = NOW() WHERE id = ?");
    $update_stmt->bind_param("ii", $new_count, $row['id']);
    $update_stmt->execute();
    $update_stmt->close();
} else {
    // Insert new record
    $insert_stmt = $conn->prepare("INSERT INTO page_views 
                                  (page_url, ip_address, user_agent, referrer, country, region, city, device_type) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("ssssssss", $page_url, $ip_address, $user_agent, $referrer, $country, $region, $city, $device_type);
    $insert_stmt->execute();
    $insert_stmt->close();
}

$stmt->close();
$conn->close();
?>