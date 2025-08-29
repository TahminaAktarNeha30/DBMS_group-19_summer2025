<?php
// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

$storageId = $_GET['id'];

$stmt = $conn->prepare("SELECT 
    facility_id AS storage_id,
    facility_name AS name,
    NULL AS type,
    location,
    capacity,
    NULL AS status,
    NULL AS entry_date
FROM storage_facilities WHERE facility_id = ?");
$stmt->bind_param("s", $storageId);
$stmt->execute();

$result = $stmt->get_result();
$facility = $result->fetch_assoc();

echo json_encode($facility);

$stmt->close();
$conn->close();
