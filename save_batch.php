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
if (!isset($data['batchId']) || !isset($data['packageCount']) || !isset($data['batchWeight']) || 
    !isset($data['qualityStatus']) || !isset($data['creationDate'])) {
    die(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

// Prepare and execute query matching current schema (map fields)
$stmt = $conn->prepare("INSERT INTO product_batches (batch_id, product_name, quantity, production_date, quality_grade) 
                       VALUES (?, '', ?, ?, ?) 
                       ON DUPLICATE KEY UPDATE 
                       quantity = VALUES(quantity), production_date = VALUES(production_date), quality_grade = VALUES(quality_grade)");

$stmt->bind_param("siss", 
    $data['batchId'], 
    $data['packageCount'], 
    $data['creationDate'],
    $data['qualityStatus']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
