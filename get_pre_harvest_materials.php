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

// Ensure table exists; if not, return empty list gracefully
$conn->query("CREATE TABLE IF NOT EXISTS pre_harvest_materials (
    pre_harvest_id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    type VARCHAR(50) NOT NULL,
    harvesting_date DATE NOT NULL,
    expiry_date DATE NOT NULL
)");

// Fetch all pre-harvest materials
$result = $conn->query("SELECT * FROM pre_harvest_materials ORDER BY harvesting_date ASC");

$materials = [];
if ($result instanceof mysqli_result) {
    while ($row = $result->fetch_assoc()) {
        $materials[] = $row;
    }
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    $conn->close();
    exit;
}

echo json_encode($materials);

$conn->close();
