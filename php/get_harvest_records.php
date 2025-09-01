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

// Fetch all harvest records
$result = $conn->query("SELECT * FROM harvest_records ORDER BY harvest_time DESC");

$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}

echo json_encode($records);

$conn->close();
