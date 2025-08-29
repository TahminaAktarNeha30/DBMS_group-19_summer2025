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
if (!isset($data['farmerId']) || !isset($data['firstName']) || 
    !isset($data['lastName']) || !isset($data['phone'])) {
    die(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

// Validate phone length (11 digits)
if (!preg_match('/^[0-9]{11}$/', $data['phone'] ?? '')) {
    die(json_encode(['success' => false, 'message' => 'Phone must be 11 digits']));
}

// Prepare and execute query matching schema (store full name and contact number)
$fullName = trim(($data['firstName'] ?? '') . ' ' . ($data['lastName'] ?? ''));
$stmt = $conn->prepare("INSERT INTO farmers (farmer_id, farmer_name, contact_number) 
                       VALUES (?, ?, ?) 
                       ON DUPLICATE KEY UPDATE 
                       farmer_name = VALUES(farmer_name), contact_number = VALUES(contact_number)");

$stmt->bind_param("sss", 
    $data['farmerId'], 
    $fullName,
    $data['phone']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
