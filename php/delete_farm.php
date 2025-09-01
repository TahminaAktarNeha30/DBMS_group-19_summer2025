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
        echo json_encode(['success' => false, 'error' => 'Farm ID is required']);
        exit;
    }
    
    $farmId = $_GET['id'];
    
    // Check if farms table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'farms'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        echo json_encode(['success' => false, 'error' => 'Farms table not found']);
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM farms WHERE farm_id = ?");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $farmId);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Farm not found or already deleted']);
        }
    } else {
        throw new Exception('Execute failed: ' . $stmt->error);
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
