<?php
// Database configuration
include_once __DIR__ . '/../config/db.php';

// Create cache directory if it doesn't exist
$cache_dir = __DIR__ . '/../cache';
if (!file_exists($cache_dir)) {
    mkdir($cache_dir, 0755, true);
}

// Create logs directory if it doesn't exist
$log_dir = __DIR__ . '/../logs';
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0755, true);
}

// Get current page URL
$page_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Get visitor IP address (with proxy handling)
$ip_address = $_SERVER['HTTP_CLIENT_IP'] ?? 
              $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
              $_SERVER['REMOTE_ADDR'];

// Sanitize IP address
$ip_address = filter_var($ip_address, FILTER_VALIDATE_IP) ? $ip_address : 'Invalid IP';

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

// Location detection with caching
$country = $region = $city = 'Unknown';
$cache_file = "$cache_dir/ip_" . md5($ip_address) . ".json";
$cache_time = 86400; // 1 day cache

// Try to get cached location first
if (file_exists($cache_file) && time() - filemtime($cache_file) < $cache_time) {
    $location_data = json_decode(file_get_contents($cache_file), true);
    if ($location_data) {
        $country = $location_data['country'] ?? 'Unknown';
        $region = $location_data['regionName'] ?? 'Unknown';
        $city = $location_data['city'] ?? 'Unknown';
    }
} else {
    // Get fresh location data from ip-api.com
    $api_url = "http://ip-api.com/json/$ip_address";
    $api_response = @file_get_contents($api_url);
    
    if ($api_response !== false) {
        $location_data = json_decode($api_response, true);
        
        if ($location_data && ($location_data['status'] ?? '') === 'success') {
            $country = $location_data['country'] ?? 'Unknown';
            $region = $location_data['regionName'] ?? 'Unknown';
            $city = $location_data['city'] ?? 'Unknown';
            
            // Cache the successful response
            file_put_contents($cache_file, json_encode([
                'country' => $country,
                'regionName' => $region,
                'city' => $city,
                'timestamp' => time()
            ]));
        } else {
            // Log API failures
            $error_msg = date('Y-m-d H:i:s') . " - IP: $ip_address - ";
            $error_msg .= isset($location_data['message']) ? $location_data['message'] : 'Unknown API error';
            file_put_contents("$log_dir/geoip_errors.log", $error_msg . PHP_EOL, FILE_APPEND);
        }
    }
}

// Database operations
try {
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
} catch (Exception $e) {
    // Log database errors
    file_put_contents("$log_dir/db_errors.log", 
        date('Y-m-d H:i:s') . " - " . $e->getMessage() . PHP_EOL, 
        FILE_APPEND);
}

$conn->close();
?>