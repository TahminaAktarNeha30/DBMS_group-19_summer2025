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
        echo json_encode(['success' => false, 'error' => 'Storage ID is required']);
        exit;
    }
    
    $storageId = $_GET['id'];
    
    $stmt = $conn->prepare("SELECT 
        facility_id AS storage_id,
        facility_name AS name,
        'warehouse' AS type,
        location,
        capacity,
        'active' AS status,
        NOW() AS entry_date,
        temperature,
        humidity
    FROM storage_facilities WHERE facility_id = ?");
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $storageId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $facility = $result->fetch_assoc();
    
    if (!$facility) {
        echo json_encode(['success' => false, 'error' => 'Storage facility not found']);
    } else {
        echo json_encode($facility);
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
