<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Ensure table exists
$conn->query("CREATE TABLE IF NOT EXISTS spoilage_records (
    record_id VARCHAR(50) PRIMARY KEY,
    batch_id VARCHAR(50),
    quantity_lost DECIMAL(10,2),
    reason VARCHAR(255),
    spoilage_date DATE
)");

$result = $conn->query("SELECT * FROM spoilage_records ORDER BY spoilage_date DESC");
$records = [];
if ($result instanceof mysqli_result) {
    while ($row = $result->fetch_assoc()) { $records[] = $row; }
}
echo json_encode($records);
$conn->close();

