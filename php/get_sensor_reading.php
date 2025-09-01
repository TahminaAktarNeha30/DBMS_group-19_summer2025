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
    
    if (!isset($_GET['id'])) {
        echo json_encode(['success' => false, 'error' => 'Sensor ID is required']);
        exit;
    }
    
    $sensorId = $_GET['id'];
    
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
    
    $query = "SELECT " . implode(', ', $selectColumns) . " FROM sensor_data WHERE sensor_id = ? ORDER BY " . $orderBy . " DESC LIMIT 1";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $sensorId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $reading = $result->fetch_assoc();
    
    echo json_encode($reading ?: ['success' => false, 'error' => 'Sensor reading not found']);
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
