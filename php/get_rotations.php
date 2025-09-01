<?php
// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Fetch all rotations
$result = $conn->query("SELECT * FROM inventory_rotations ORDER BY rotation_date DESC");

$rotations = [];
while ($row = $result->fetch_assoc()) {
    $rotations[] = $row;
}

echo json_encode($rotations);

$conn->close();
