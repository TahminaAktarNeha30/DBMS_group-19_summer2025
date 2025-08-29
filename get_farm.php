<?php
// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

$farmId = $_GET['id'];

$stmt = $conn->prepare("SELECT 
    farm_id,
    location,
    total_area AS size,
    soil_type,
    '' AS irrigation_method
FROM farms WHERE farm_id = ?");
$stmt->bind_param("s", $farmId);
$stmt->execute();

$result = $stmt->get_result();
$farm = $result->fetch_assoc();

echo json_encode($farm);

$stmt->close();
$conn->close();
