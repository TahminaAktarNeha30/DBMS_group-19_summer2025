<?php
// Ensure JSON output and suppress HTML errors
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

try {
    // Database connection
    $db_config = include 'config.php';
    if (!$db_config) {
        throw new Exception('Config file not found');
    }
    
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
    
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    
    if (!isset($_GET['id'])) {
        echo json_encode(['success' => false, 'error' => 'Batch ID is required']);
        exit;
    }
    
    $batchId = $_GET['id'];
    
    $stmt = $conn->prepare("SELECT 
        batch_id,
        quantity AS package_count,
        weight AS batch_weight,
        quality_grade AS quality_status,
        production_date AS creation_date,
        product_name
    FROM product_batches WHERE batch_id = ?");
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $batchId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $batch = $result->fetch_assoc();
    
    if (!$batch) {
        echo json_encode(['success' => false, 'error' => 'Batch not found']);
    } else {
        echo json_encode($batch);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
