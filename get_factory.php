<?php
// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

$factoryId = $_GET['id'];

$stmt = $conn->prepare("SELECT 
    factory_id,
    factory_name AS name,
    location,
    capacity AS processing_capacity,
    status AS capacity_unit
FROM factories WHERE factory_id = ?");
$stmt->bind_param("s", $factoryId);
$stmt->execute();

$result = $stmt->get_result();
$factory = $result->fetch_assoc();

echo json_encode($factory);

$stmt->close();
$conn->close();
