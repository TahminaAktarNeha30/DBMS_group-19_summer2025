<?php
// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Fetch all factories with aliases matching frontend expectations
$result = $conn->query("SELECT 
    factory_id,
    factory_name AS name,
    location,
    capacity AS processing_capacity,
    status AS capacity_unit
FROM factories
ORDER BY factory_name ASC");

$factories = [];
while ($row = $result->fetch_assoc()) {
    $factories[] = $row;
}

echo json_encode($factories);

$conn->close();
