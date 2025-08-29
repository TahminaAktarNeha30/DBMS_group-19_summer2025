<?php
// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate data
if (!isset($data['storageId']) || !isset($data['name']) || !isset($data['type']) || 
    !isset($data['location']) || !isset($data['capacity']) || !isset($data['status']) || 
    !isset($data['entryDate'])) {
    die(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

// Prepare and execute query mapping to schema
$stmt = $conn->prepare("INSERT INTO storage_facilities (facility_id, facility_name, location, capacity) 
                        VALUES (?, ?, ?, ?) 
                        ON DUPLICATE KEY UPDATE 
                        facility_name = VALUES(facility_name), location = VALUES(location), capacity = VALUES(capacity)");

$stmt->bind_param("sssd", 
    $data['storageId'], 
    $data['name'],
    $data['location'],
    $data['capacity']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
