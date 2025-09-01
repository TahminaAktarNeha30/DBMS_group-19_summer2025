<?php
// Ensure JSON output and suppress HTML errors
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

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
    
    // Check if farms table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'farms'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        echo json_encode([]);
        exit;
    }
    
    // Get table structure for dynamic field mapping
    $columnsResult = $conn->query("SHOW COLUMNS FROM farms");
    $availableColumns = [];
    while ($column = $columnsResult->fetch_assoc()) {
        $availableColumns[] = $column['Field'];
    }
    
    // Build dynamic query based on available columns
    $selectFields = [];
    $selectFields[] = 'farm_id';
    $selectFields[] = 'location';
    
    // Handle size/total_area field variation
    if (in_array('size', $availableColumns)) {
        $selectFields[] = 'size';
    } elseif (in_array('total_area', $availableColumns)) {
        $selectFields[] = 'total_area AS size';
    } else {
        $selectFields[] = "'' AS size";
    }
    
    $selectFields[] = in_array('soil_type', $availableColumns) ? 'soil_type' : "'' AS soil_type";
    $selectFields[] = in_array('irrigation_method', $availableColumns) ? 'irrigation_method' : "'' AS irrigation_method";
    
    $query = "SELECT " . implode(', ', $selectFields) . " FROM farms ORDER BY location ASC";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $farms = [];
    while ($row = $result->fetch_assoc()) {
        $farms[] = $row;
    }
    
    echo json_encode($farms);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
