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

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Sensor ID is required']);
    exit;
}

$sensorId = $_GET['id'];

$stmt = $conn->prepare("SELECT 
    sensor_id,
    humidity,
    NULL AS oxygen_level,
    NULL AS ph_level,
    temperature,
    reading_time AS reading_timestamp
FROM sensor_data WHERE sensor_id = ? ORDER BY reading_time DESC LIMIT 1");
$stmt->bind_param("s", $sensorId);
$stmt->execute();

$result = $stmt->get_result();
$reading = $result->fetch_assoc();

echo json_encode($reading ?: []);

$stmt->close();
$conn->close();
