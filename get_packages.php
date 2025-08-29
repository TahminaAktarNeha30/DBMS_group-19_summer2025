<?php
// Ensure JSON output and suppress HTML errors
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Connection failed: ' . $conn->connect_error]));
}

// Fetch all packages and alias to frontend fields
$result = $conn->query("SELECT 
    package_id,
    '' AS name,
    NULL AS packaging_info,
    package_type AS type,
    weight,
    packaging_date AS production_date,
    NULL AS expiration_date
FROM packages
ORDER BY packaging_date DESC");

$packages = [];
while ($row = $result->fetch_assoc()) {
    $packages[] = $row;
}

echo json_encode(['success' => true, 'data' => $packages]);

$conn->close();
