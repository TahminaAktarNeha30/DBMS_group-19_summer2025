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
    
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required data
    if (!$data || !isset($data['sensorId']) || !isset($data['readingTimestamp'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields: sensorId and readingTimestamp']);
        exit;
    }
    
    // Check table structure to determine available columns
    $columnsResult = $conn->query("SHOW COLUMNS FROM sensor_data");
    $availableColumns = [];
    while ($column = $columnsResult->fetch_assoc()) {
        $availableColumns[] = $column['Field'];
    }
    
    // Prepare variables for binding (bind_param requires references)
    $sensorId = $data['sensorId'];
    $readingTimestamp = $data['readingTimestamp'];
    $humidity = isset($data['humidity']) ? floatval($data['humidity']) : null;
    $oxygenLevel = isset($data['oxygenLevel']) ? floatval($data['oxygenLevel']) : null;
    $phLevel = isset($data['pH']) ? floatval($data['pH']) : null;
    $temperature = isset($data['temperature']) ? floatval($data['temperature']) : null;
    
    // Build dynamic query based on available columns
    $insertColumns = ['sensor_id'];
    $insertValues = ['?'];
    $updateColumns = [];
    $bindTypes = 's';
    $bindValues = [&$sensorId];
    
    // Handle timestamp column
    if (in_array('reading_timestamp', $availableColumns)) {
        $insertColumns[] = 'reading_timestamp';
        $insertValues[] = '?';
        $updateColumns[] = 'reading_timestamp = VALUES(reading_timestamp)';
        $bindTypes .= 's';
        $bindValues[] = &$readingTimestamp;
    } else if (in_array('reading_time', $availableColumns)) {
        $insertColumns[] = 'reading_time';
        $insertValues[] = '?';
        $updateColumns[] = 'reading_time = VALUES(reading_time)';
        $bindTypes .= 's';
        $bindValues[] = &$readingTimestamp;
    }
    
    // Add other columns if they exist
    if (in_array('humidity', $availableColumns) && $humidity !== null) {
        $insertColumns[] = 'humidity';
        $insertValues[] = '?';
        $updateColumns[] = 'humidity = VALUES(humidity)';
        $bindTypes .= 'd';
        $bindValues[] = &$humidity;
    }
    
    if (in_array('oxygen_level', $availableColumns) && $oxygenLevel !== null) {
        $insertColumns[] = 'oxygen_level';
        $insertValues[] = '?';
        $updateColumns[] = 'oxygen_level = VALUES(oxygen_level)';
        $bindTypes .= 'd';
        $bindValues[] = &$oxygenLevel;
    }
    
    if (in_array('ph_level', $availableColumns) && $phLevel !== null) {
        $insertColumns[] = 'ph_level';
        $insertValues[] = '?';
        $updateColumns[] = 'ph_level = VALUES(ph_level)';
        $bindTypes .= 'd';
        $bindValues[] = &$phLevel;
    }
    
    if (in_array('temperature', $availableColumns) && $temperature !== null) {
        $insertColumns[] = 'temperature';
        $insertValues[] = '?';
        $updateColumns[] = 'temperature = VALUES(temperature)';
        $bindTypes .= 'd';
        $bindValues[] = &$temperature;
    }
    
    // Build the complete query
    $query = "INSERT INTO sensor_data (" . implode(', ', $insertColumns) . ") VALUES (" . implode(', ', $insertValues) . ")";
    if (!empty($updateColumns)) {
        $query .= " ON DUPLICATE KEY UPDATE " . implode(', ', $updateColumns);
    }
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    // Bind parameters dynamically
    if (!empty($bindValues)) {
        $stmt->bind_param($bindTypes, ...$bindValues);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Sensor data saved successfully']);
    } else {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
