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
    
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate data - match frontend form fields
    if (!$data || !isset($data['storageId']) || !isset($data['name']) || !isset($data['location'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    // Prepare and execute query matching actual database schema
    $stmt = $conn->prepare("INSERT INTO storage_facilities (facility_id, facility_name, location, capacity, temperature, humidity) 
                           VALUES (?, ?, ?, ?, ?, ?) 
                           ON DUPLICATE KEY UPDATE 
                           facility_name = VALUES(facility_name), location = VALUES(location), capacity = VALUES(capacity), temperature = VALUES(temperature), humidity = VALUES(humidity)");
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    // Prepare variables for binding (bind_param requires references)
    $storageId = $data['storageId'];
    $name = $data['name'];
    $location = $data['location'];
    $capacity = $data['capacity'] ?? null;
    $temperature = $data['temperature'] ?? null;
    $humidity = $data['humidity'] ?? null;
    
    $stmt->bind_param("sssidd", 
        $storageId, 
        $name,
        $location,
        $capacity,
        $temperature,
        $humidity
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
