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
    
    // Check if suppliers table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'suppliers'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        // If table doesn't exist, return empty array
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }
    
    // Fetch all suppliers
    $result = $conn->query("SELECT 
        supplier_id,
        supplier_name,
        contact_person,
        contact_number,
        email,
        address,
        registration_date,
        status
    FROM suppliers
    WHERE status = 'active'
    ORDER BY supplier_name ASC");
    
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $suppliers = [];
    while ($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $suppliers]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>