<?php
// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Fetch all storage facilities with aliases to match frontend expectations
$result = $conn->query("SELECT 
    facility_id AS storage_id,
    facility_name AS name,
    NULL AS type,
    location,
    capacity,
    NULL AS status,
    NULL AS entry_date
FROM storage_facilities
ORDER BY facility_name ASC");

$facilities = [];
while ($row = $result->fetch_assoc()) {
    $facilities[] = $row;
}

echo json_encode($facilities);

$conn->close();
