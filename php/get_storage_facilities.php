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
    
    // Check if storage_facilities table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'storage_facilities'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }
    
    // Fetch all storage facilities with correct field mapping
    $result = $conn->query("SELECT 
        facility_id AS storage_id,
        facility_name AS name,
        'warehouse' AS type,
        location,
        capacity,
        'active' AS status,
        NOW() AS entry_date,
        temperature,
        humidity
    FROM storage_facilities
    ORDER BY facility_name ASC");
    
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $facilities = [];
    while ($row = $result->fetch_assoc()) {
        $facilities[] = $row;
    }
    
    echo json_encode($facilities);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
