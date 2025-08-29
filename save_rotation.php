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
if (!isset($data['rotationId']) || !isset($data['strategy']) || 
    !isset($data['rotationDate']) || !isset($data['rotationType'])) {
    die(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

// Prepare and execute query
$stmt = $conn->prepare("INSERT INTO inventory_rotations (rotation_id, strategy, rotation_date, rotation_type) 
                       VALUES (?, ?, ?, ?) 
                       ON DUPLICATE KEY UPDATE 
                       strategy = ?, rotation_date = ?, rotation_type = ?");

$stmt->bind_param("sssssss", 
    $data['rotationId'], 
    $data['strategy'],
    $data['rotationDate'],
    $data['rotationType'],
    $data['strategy'],
    $data['rotationDate'],
    $data['rotationType']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
