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
    
    // Check if perishable_products table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'perishable_products'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        throw new Exception('Perishable products table does not exist. Please run the database setup.');
    }
    
    // Fetch all perishable products
    $result = $conn->query("SELECT 
        product_id,
        product_name,
        category,
        shelf_life_days,
        packaging_type,
        quantity,
        created_at,
        updated_at
    FROM perishable_products
    ORDER BY created_at DESC");
    
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $products]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>