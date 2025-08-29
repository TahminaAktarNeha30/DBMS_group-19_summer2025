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

// Validate data (map to existing schema: temperature, humidity, reading_time)
if (!isset($data['sensorId']) || !isset($data['humidity']) || !isset($data['temperature']) || !isset($data['readingTimestamp'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO sensor_data (sensor_id, temperature, humidity, reading_time) 
                        VALUES (?, ?, ?, ?) 
                        ON DUPLICATE KEY UPDATE 
                        temperature = VALUES(temperature), humidity = VALUES(humidity), reading_time = VALUES(reading_time)");

$stmt->bind_param("sdds", 
    $data['sensorId'], 
    $data['temperature'],
    $data['humidity'],
    $data['readingTimestamp']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
