<?php
// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Fetch all farms with aliases to match frontend expectations
$result = $conn->query("SELECT 
    farm_id,
    location,
    total_area AS size,
    soil_type,
    '' AS irrigation_method
FROM farms
ORDER BY location ASC");

$farms = [];
while ($row = $result->fetch_assoc()) {
    $farms[] = $row;
}

echo json_encode($farms);

$conn->close();
