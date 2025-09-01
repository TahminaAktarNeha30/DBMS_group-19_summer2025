<?php
// Simple test to check farmers table
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $db_config = include 'config.php';
    if (!$db_config) {
        throw new Exception('Config file not found');
    }
    
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
    
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    
    echo json_encode([
        'status' => 'connected',
        'database' => $db_config['database']
    ]);
    
    // Check if farmers table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'farmers'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        echo json_encode([
            'status' => 'table_missing',
            'message' => 'farmers table does not exist'
        ]);
    } else {
        // Check table structure
        $structure = $conn->query("DESCRIBE farmers");
        $columns = [];
        while ($row = $structure->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        echo json_encode([
            'status' => 'success',
            'table_exists' => true,
            'columns' => $columns
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'error' => $e->getMessage()
    ]);
}