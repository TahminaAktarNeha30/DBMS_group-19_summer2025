<?php
// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Fetch all batches with aliases to match frontend expectations
$result = $conn->query("SELECT 
    batch_id,
    quantity AS package_count,
    NULL AS batch_weight,
    quality_grade AS quality_status,
    production_date AS creation_date
FROM product_batches
ORDER BY batch_id DESC");

$batches = [];
while ($row = $result->fetch_assoc()) {
    $batches[] = $row;
}

echo json_encode($batches);

$conn->close();
