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
    
    // Get table structure for dynamic field mapping
    $columnsResult = $conn->query("SHOW COLUMNS FROM farms");
    $availableColumns = [];
    while ($column = $columnsResult->fetch_assoc()) {
        $availableColumns[] = $column['Field'];
    }
    
    // Build dynamic query based on available columns
    $selectFields = [];
    $selectFields[] = 'farm_id';
    $selectFields[] = 'location';
    
    // Handle size/total_area field variation
    if (in_array('size', $availableColumns)) {
        $selectFields[] = 'size';
    } elseif (in_array('total_area', $availableColumns)) {
        $selectFields[] = 'total_area AS size';
    } else {
        $selectFields[] = "'' AS size";
    }
    
    $selectFields[] = in_array('soil_type', $availableColumns) ? 'soil_type' : "'' AS soil_type";
    $selectFields[] = in_array('irrigation_method', $availableColumns) ? 'irrigation_method' : "'' AS irrigation_method";
    
    $query = "SELECT " . implode(', ', $selectFields) . " FROM farms WHERE farm_id = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $farmId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $farm = $result->fetch_assoc();
    
    if (!$farm) {
        echo json_encode(['success' => false, 'error' => 'Farm not found']);
    } else {
        echo json_encode($farm);
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
