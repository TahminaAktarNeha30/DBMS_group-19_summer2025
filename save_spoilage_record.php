<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['recordId']) || !isset($data['quantityLost']) || !isset($data['spoilageDate'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Ensure table exists
$conn->query("CREATE TABLE IF NOT EXISTS spoilage_records (
    record_id VARCHAR(50) PRIMARY KEY,
    batch_id VARCHAR(50),
    quantity_lost DECIMAL(10,2),
    reason VARCHAR(255),
    spoilage_date DATE
)");

$stmt = $conn->prepare("INSERT INTO spoilage_records (record_id, batch_id, quantity_lost, reason, spoilage_date) 
                        VALUES (?, ?, ?, ?, ?) 
                        ON DUPLICATE KEY UPDATE 
                        batch_id = VALUES(batch_id), quantity_lost = VALUES(quantity_lost), reason = VALUES(reason), spoilage_date = VALUES(spoilage_date)");
$stmt->bind_param("ssdss", 
    $data['recordId'],
    $data['batchId'],
    $data['quantityLost'],
    $data['reason'],
    $data['spoilageDate']
);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();
$conn->close();

