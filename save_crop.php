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
if (!isset($data['cropId']) || !isset($data['cropName']) || !isset($data['category'])) {
    die(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

// Prepare and execute query matching schema
$stmt = $conn->prepare("INSERT INTO crops (crop_id, crop_name, status) 
                       VALUES (?, ?, ?) 
                       ON DUPLICATE KEY UPDATE 
                       crop_name = VALUES(crop_name), status = VALUES(status)");

$stmt->bind_param("sss", 
    $data['cropId'], 
    $data['cropName'],
    $data['category']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
