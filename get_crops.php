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

// Fetch all crops with aliases to match frontend expectations
$result = $conn->query("SELECT 
    crop_id,
    crop_name,
    status AS category,
    NULL AS water_requirements,
    NULL AS soil_preference
FROM crops
ORDER BY crop_name ASC");

$crops = [];
while ($row = $result->fetch_assoc()) {
    $crops[] = $row;
}

echo json_encode($crops);

$conn->close();
