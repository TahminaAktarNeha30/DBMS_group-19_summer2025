<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Record ID is required']);
    exit;
}

$recordId = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM spoilage_records WHERE record_id = ?");
$stmt->bind_param("s", $recordId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
echo json_encode($row ?: []);
$stmt->close();
$conn->close();

