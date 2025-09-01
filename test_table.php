<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $db_config = include 'php/config.php';
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
    
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    
    // Check table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'perishable_products'");
    $tableExists = $tableCheck && $tableCheck->num_rows > 0;
    
    if (!$tableExists) {
        echo json_encode(['success' => false, 'error' => 'Table does not exist', 'action' => 'create']);
        exit;
    }
    
    // Get count
    $countResult = $conn->query("SELECT COUNT(*) as count FROM perishable_products");
    $count = $countResult->fetch_assoc()['count'];
    
    echo json_encode(['success' => true, 'table_exists' => true, 'record_count' => $count]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>