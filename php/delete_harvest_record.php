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
    echo json_encode(['success' => false, 'message' => 'Record ID is required']);
    exit;
}

$recordId = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM harvest_records WHERE record_id = ?");
$stmt->bind_param("s", $recordId);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Record not found']);
} else {
    echo json_encode(['success' => true]);
}

$stmt->close();
$conn->close();
