<?php
// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Fetch all markets with aliases to match frontend expectations
$result = $conn->query("SELECT 
    market_id,
    market_name AS name,
    location,
    market_type AS type,
    contact_info AS contact_person,
    NULL AS operational_hours,
    0 AS quantity
FROM markets
ORDER BY market_name ASC");

$markets = [];
while ($row = $result->fetch_assoc()) {
    $markets[] = $row;
}

echo json_encode($markets);

$conn->close();
