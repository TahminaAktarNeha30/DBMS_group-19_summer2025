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

// Fetch sensor data ordered by timestamp, aliasing to match frontend expectations
$result = $conn->query("SELECT 
    sensor_id,
    humidity,
    NULL AS oxygen_level,
    NULL AS ph_level,
    temperature,
    reading_time AS reading_timestamp
FROM sensor_data
ORDER BY reading_time DESC
LIMIT 100");

$readings = [];
while ($row = $result->fetch_assoc()) {
    $readings[] = $row;
}

echo json_encode($readings);

$conn->close();
