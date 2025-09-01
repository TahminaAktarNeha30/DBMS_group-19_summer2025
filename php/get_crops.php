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
    
    // Check if crops table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'crops'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        echo json_encode([]);
        exit;
    }
    
    // Get table structure for dynamic field mapping
    $columnsResult = $conn->query("SHOW COLUMNS FROM crops");
    $availableColumns = [];
    while ($column = $columnsResult->fetch_assoc()) {
        $availableColumns[] = $column['Field'];
    }
    
    // Build dynamic query based on available columns
    $selectFields = [];
    $selectFields[] = in_array('crop_id', $availableColumns) ? 'crop_id' : "'' AS crop_id";
    $selectFields[] = in_array('crop_name', $availableColumns) ? 'crop_name' : "'' AS crop_name";
    $selectFields[] = in_array('category', $availableColumns) ? 'category' : "'' AS category";
    $selectFields[] = in_array('water_requirements', $availableColumns) ? 'water_requirements' : "'' AS water_requirements";
    $selectFields[] = in_array('soil_preference', $availableColumns) ? 'soil_preference' : "'' AS soil_preference";
    
    $query = "SELECT " . implode(', ', $selectFields) . " FROM crops ORDER BY crop_name ASC";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $crops = [];
    while ($row = $result->fetch_assoc()) {
        $crops[] = $row;
    }
    
    echo json_encode($crops);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
