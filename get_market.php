<?php
// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

$marketId = $_GET['id'];

$stmt = $conn->prepare("SELECT 
    market_id,
    market_name AS name,
    location,
    market_type AS type,
    contact_info AS contact_person,
    NULL AS operational_hours,
    0 AS quantity
FROM markets WHERE market_id = ?");
$stmt->bind_param("s", $marketId);
$stmt->execute();

$result = $stmt->get_result();
$market = $result->fetch_assoc();

echo json_encode($market);

$stmt->close();
$conn->close();
