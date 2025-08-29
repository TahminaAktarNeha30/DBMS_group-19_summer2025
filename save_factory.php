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
if (!isset($data['factoryId']) || !isset($data['name']) || !isset($data['location']) || 
    !isset($data['processingCapacity']) || !isset($data['capacityUnit'])) {
    die(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

// Prepare and execute query matching schema
$stmt = $conn->prepare("INSERT INTO factories (factory_id, factory_name, location, capacity, status) 
                       VALUES (?, ?, ?, ?, ?) 
                       ON DUPLICATE KEY UPDATE 
                       factory_name = VALUES(factory_name), location = VALUES(location), capacity = VALUES(capacity), status = VALUES(status)");

$stmt->bind_param("sssds", 
    $data['factoryId'], 
    $data['name'],
    $data['location'],
    $data['processingCapacity'],
    $data['capacityUnit']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
