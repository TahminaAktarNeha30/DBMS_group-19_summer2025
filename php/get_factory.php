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
        echo json_encode(['success' => false, 'error' => 'Factory ID is required']);
        exit;
    }
    
    $factoryId = $_GET['id'];
    
    $stmt = $conn->prepare("SELECT 
        factory_id,
        factory_name AS name,
        location,
        capacity AS processing_capacity,
        status AS capacity_unit
    FROM factories WHERE factory_id = ?");
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $factoryId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $factory = $result->fetch_assoc();
    
    if (!$factory) {
        echo json_encode(['success' => false, 'error' => 'Factory not found']);
    } else {
        echo json_encode($factory);
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
