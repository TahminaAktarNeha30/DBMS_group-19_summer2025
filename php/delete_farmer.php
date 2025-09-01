<?php
// Ensure JSON output and suppress HTML errors
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
ini_set('html_errors', 0);

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
    
    $farmerId = $_GET['id'] ?? '';
    if (empty($farmerId)) {
        throw new Exception('Farmer ID is required');
    }
    
    $stmt = $conn->prepare("DELETE FROM farmers WHERE farmer_id = ?");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $farmerId);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Farmer deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Farmer not found']);
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
