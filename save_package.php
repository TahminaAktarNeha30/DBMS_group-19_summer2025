<?php
// Ensure JSON output and suppress HTML errors
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Connection failed: ' . $conn->connect_error]));
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input (name optional per schema, map fields)
if (!$data || !isset($data['packageId']) || !isset($data['type']) || !isset($data['weight'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO packages (package_id, batch_id, package_type, weight, packaging_date) 
                        VALUES (?, NULL, ?, ?, ?) 
                        ON DUPLICATE KEY UPDATE 
                        package_type = VALUES(package_type), weight = VALUES(weight), packaging_date = VALUES(packaging_date)");

$stmt->bind_param("ssds", 
    $data['packageId'],
    $data['type'],
    $data['weight'],
    $data['productionDate']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
