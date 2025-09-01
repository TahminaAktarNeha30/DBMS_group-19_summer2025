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
    
    // Check if sensor_data table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'sensor_data'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }
    
    // Check table structure to determine available columns
    $columnsResult = $conn->query("SHOW COLUMNS FROM sensor_data");
    $availableColumns = [];
    while ($column = $columnsResult->fetch_assoc()) {
        $availableColumns[] = $column['Field'];
    }
    
    // Build query based on available columns
    $selectColumns = [];
    $selectColumns[] = 'sensor_id';
    
    if (in_array('humidity', $availableColumns)) {
        $selectColumns[] = 'humidity';
    } else {
        $selectColumns[] = 'NULL AS humidity';
    }
    
    if (in_array('oxygen_level', $availableColumns)) {
        $selectColumns[] = 'oxygen_level';
    } else {
        $selectColumns[] = 'NULL AS oxygen_level';
    }
    
    if (in_array('ph_level', $availableColumns)) {
        $selectColumns[] = 'ph_level';
    } else {
        $selectColumns[] = 'NULL AS ph_level';
    }
    
    if (in_array('temperature', $availableColumns)) {
        $selectColumns[] = 'temperature';
    } else {
        $selectColumns[] = 'NULL AS temperature';
    }
    
    // Handle timestamp column variations
    if (in_array('reading_timestamp', $availableColumns)) {
        $selectColumns[] = 'reading_timestamp';
        $orderBy = 'reading_timestamp';
    } else if (in_array('reading_time', $availableColumns)) {
        $selectColumns[] = 'reading_time AS reading_timestamp';
        $orderBy = 'reading_time';
    } else {
        $selectColumns[] = 'NOW() AS reading_timestamp';
        $orderBy = 'sensor_id';
    }
    
    $query = "SELECT " . implode(', ', $selectColumns) . " FROM sensor_data ORDER BY " . $orderBy . " DESC LIMIT 100";
    
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $readings = [];
    while ($row = $result->fetch_assoc()) {
        $readings[] = $row;
    }
    
    echo json_encode($readings);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage(), 'data' => []]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
