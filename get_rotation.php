<?php
// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

$rotationId = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM inventory_rotations WHERE rotation_id = ?");
$stmt->bind_param("s", $rotationId);
$stmt->execute();

$result = $stmt->get_result();
$rotation = $result->fetch_assoc();

echo json_encode($rotation);

$stmt->close();
$conn->close();
