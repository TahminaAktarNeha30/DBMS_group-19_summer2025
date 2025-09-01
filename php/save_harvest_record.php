<?php
// Ensure JSON output and suppress HTML errors
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!$data || !isset($data['recordId']) || !isset($data['status']) || 
    !isset($data['quantity']) || !isset($data['harvestTime']) || !isset($data['sowingDate'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO harvest_records (record_id, status, quantity, harvest_time, sowing_date) 
                        VALUES (?, ?, ?, ?, ?) 
                        ON DUPLICATE KEY UPDATE 
                        status = VALUES(status),
                        quantity = VALUES(quantity),
                        harvest_time = VALUES(harvest_time),
                        sowing_date = VALUES(sowing_date)");

$stmt->bind_param("ssiss", 
    $data['recordId'], 
    $data['status'],
    $data['quantity'],
    $data['harvestTime'],
    $data['sowingDate']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
