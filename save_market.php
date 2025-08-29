<?php
// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate data (map to schema)
if (!isset($data['marketId']) || !isset($data['name']) || !isset($data['type']) || !isset($data['location'])) {
    die(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

// Prepare and execute query matching schema
$stmt = $conn->prepare("INSERT INTO markets (market_id, market_name, location, market_type, contact_info) 
                       VALUES (?, ?, ?, ?, ?) 
                       ON DUPLICATE KEY UPDATE 
                       market_name = VALUES(market_name), location = VALUES(location), market_type = VALUES(market_type), contact_info = VALUES(contact_info)");

$stmt->bind_param("sssss", 
    $data['marketId'], 
    $data['name'],
    $data['location'],
    $data['type'],
    $data['contactPerson']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
