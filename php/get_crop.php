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
        echo json_encode(['success' => false, 'error' => 'Crop ID is required']);
        exit;
    }
    
    $cropId = $_GET['id'];
    
    // Check if crops table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'crops'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        echo json_encode(['success' => false, 'error' => 'Crops table not found']);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT * FROM crops WHERE crop_id = ?");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $cropId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $crop = $result->fetch_assoc();
    
    if (!$crop) {
        echo json_encode(['success' => false, 'error' => 'Crop not found']);
    } else {
        echo json_encode($crop);
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
