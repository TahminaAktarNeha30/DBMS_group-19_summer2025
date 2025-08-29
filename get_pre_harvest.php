<?php
// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

$preHarvestId = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM pre_harvest_materials WHERE pre_harvest_id = ?");
$stmt->bind_param("s", $preHarvestId);
$stmt->execute();

$result = $stmt->get_result();
$material = $result->fetch_assoc();

echo json_encode($material);

$stmt->close();
$conn->close();
