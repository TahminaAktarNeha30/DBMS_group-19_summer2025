<?php
// Ensure JSON output and suppress HTML errors
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
ini_set('html_errors', 0);

try {
    // Database connection
    $db_config = include 'config.php';
    if (!$db_config) {
        throw new Exception('Config file not found');
    }
    
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
    
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    
    // Check if packages table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'packages'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        // If table doesn't exist, return empty array instead of error
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }
    
    // Fetch all packages with error handling - using updated schema fields
    $result = $conn->query("SELECT 
        package_id,
        name,
        packaging_info,
        type,
        weight,
        production_date,
        expiration_date
    FROM packages
    ORDER BY created_at DESC");
    
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $packages = [];
    while ($row = $result->fetch_assoc()) {
        $packages[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $packages]);
    
} catch (Exception $e) {
    // Return JSON error instead of HTML
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
