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

// Validate input - match frontend form fields
if (!$data || !isset($data['packageId']) || !isset($data['type']) || !isset($data['weight'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

// Prepare and execute query matching updated schema
$stmt = $conn->prepare("INSERT INTO packages (package_id, name, packaging_info, type, weight, production_date, expiration_date) 
                        VALUES (?, ?, ?, ?, ?, ?, ?) 
                        ON DUPLICATE KEY UPDATE 
                        name = VALUES(name), packaging_info = VALUES(packaging_info), type = VALUES(type), weight = VALUES(weight), production_date = VALUES(production_date), expiration_date = VALUES(expiration_date)");

$stmt->bind_param("ssssdss", 
    $data['packageId'],
    $data['name'],
    $data['packagingInfo'],
    $data['type'],
    $data['weight'],
    $data['productionDate'],
    $data['expirationDate']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
