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
    
    // Check if product_batches table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'product_batches'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }
    
    // Fetch all batches with correct field mapping
    $result = $conn->query("SELECT 
        batch_id,
        quantity AS package_count,
        weight AS batch_weight,
        quality_grade AS quality_status,
        production_date AS creation_date,
        product_name
    FROM product_batches
    ORDER BY created_at DESC");
    
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $batches = [];
    while ($row = $result->fetch_assoc()) {
        $batches[] = $row;
    }
    
    echo json_encode($batches);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
