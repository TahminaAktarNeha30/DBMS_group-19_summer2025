<?php
// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

$cropId = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM crops WHERE crop_id = ?");
$stmt->bind_param("s", $cropId);
$stmt->execute();

$result = $stmt->get_result();
$crop = $result->fetch_assoc();

echo json_encode($crop);

$stmt->close();
$conn->close();
