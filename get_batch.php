<?php
// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

$batchId = $_GET['id'];

$stmt = $conn->prepare("SELECT 
    batch_id,
    quantity AS package_count,
    NULL AS batch_weight,
    quality_grade AS quality_status,
    production_date AS creation_date
FROM product_batches WHERE batch_id = ?");
$stmt->bind_param("s", $batchId);
$stmt->execute();

$result = $stmt->get_result();
$batch = $result->fetch_assoc();

echo json_encode($batch);

$stmt->close();
$conn->close();
