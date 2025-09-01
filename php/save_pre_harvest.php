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
if (!isset($data['preHarvestId']) || !isset($data['name']) || !isset($data['quantity']) || 
    !isset($data['type']) || !isset($data['harvestingDate']) || !isset($data['expiryDate'])) {
    die(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

// Prepare and execute query
$stmt = $conn->prepare("INSERT INTO pre_harvest_materials (pre_harvest_id, name, quantity, type, 
                        harvesting_date, expiry_date) 
                       VALUES (?, ?, ?, ?, ?, ?) 
                       ON DUPLICATE KEY UPDATE 
                       name = ?, quantity = ?, type = ?, harvesting_date = ?, expiry_date = ?");

$stmt->bind_param("ssdssssdsss", 
    $data['preHarvestId'], 
    $data['name'],
    $data['quantity'],
    $data['type'],
    $data['harvestingDate'],
    $data['expiryDate'],
    $data['name'],
    $data['quantity'],
    $data['type'],
    $data['harvestingDate'],
    $data['expiryDate']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
