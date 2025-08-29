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
if (!isset($data['farmId']) || !isset($data['location']) || !isset($data['size']) || !isset($data['soilType'])) {
    die(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

// Prepare and execute query matching schema
$stmt = $conn->prepare("INSERT INTO farms (farm_id, location, total_area, soil_type) 
                       VALUES (?, ?, ?, ?) 
                       ON DUPLICATE KEY UPDATE 
                       location = VALUES(location), total_area = VALUES(total_area), soil_type = VALUES(soil_type)");

$stmt->bind_param("ssds", 
    $data['farmId'], 
    $data['location'],
    $data['size'],
    $data['soilType']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
