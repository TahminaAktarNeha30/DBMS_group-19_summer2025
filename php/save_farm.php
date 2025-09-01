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
    
    // Get POST data
    $input = file_get_contents('php://input');
    if (!$input) {
        throw new Exception('No input data received');
    }
    
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }
    
    // Validate required fields
    if (!isset($data['farmId']) || !isset($data['location']) || !isset($data['size']) || !isset($data['soilType'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
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
    
    // Build dynamic INSERT query based on available columns
    $insertFields = ['farm_id', 'location'];
    $placeholders = ['?', '?'];
    $paramValues = [$data['farmId'], $data['location']];
    $paramTypes = 'ss';
    
    // Handle size field (could be 'size' or 'total_area')
    if (in_array('size', $availableColumns)) {
        $insertFields[] = 'size';
        $placeholders[] = '?';
        $paramValues[] = $data['size'];
        $paramTypes .= 'd';
    } elseif (in_array('total_area', $availableColumns)) {
        $insertFields[] = 'total_area';
        $placeholders[] = '?';
        $paramValues[] = $data['size'];
        $paramTypes .= 'd';
    }
    
    // Add soil_type if available
    if (in_array('soil_type', $availableColumns)) {
        $insertFields[] = 'soil_type';
        $placeholders[] = '?';
        $paramValues[] = $data['soilType'];
        $paramTypes .= 's';
    }
    
    // Add irrigation_method if available and provided
    if (in_array('irrigation_method', $availableColumns) && isset($data['irrigationMethod'])) {
        $insertFields[] = 'irrigation_method';
        $placeholders[] = '?';
        $paramValues[] = $data['irrigationMethod'];
        $paramTypes .= 's';
    }
    
    // Build the complete query with ON DUPLICATE KEY UPDATE
    $query = "INSERT INTO farms (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    
    // Add ON DUPLICATE KEY UPDATE clause
    $updateClauses = [];
    for ($i = 1; $i < count($insertFields); $i++) { // Skip farm_id (index 0)
        $updateClauses[] = $insertFields[$i] . " = VALUES(" . $insertFields[$i] . ")";
    }
    if (!empty($updateClauses)) {
        $query .= " ON DUPLICATE KEY UPDATE " . implode(', ', $updateClauses);
    }
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    // Bind parameters dynamically
    $stmt->bind_param($paramTypes, ...$paramValues);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
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
