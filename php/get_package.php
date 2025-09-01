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

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Package ID is required']);
    exit;
}

$packageId = $_GET['id'];

$stmt = $conn->prepare("SELECT 
    package_id,
    name,
    packaging_info,
    type,
    weight,
    production_date,
    expiration_date
FROM packages WHERE package_id = ?");
$stmt->bind_param("s", $packageId);
$stmt->execute();
$result = $stmt->get_result();
$package = $result->fetch_assoc();

if (!$package) {
    echo json_encode(['success' => false, 'error' => 'Package not found']);
} else {
    echo json_encode($package);
}

$stmt->close();
$conn->close();
