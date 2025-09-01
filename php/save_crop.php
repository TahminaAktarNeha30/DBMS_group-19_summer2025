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
    if (!isset($data['cropId']) || !isset($data['cropName']) || !isset($data['category'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    // Check if crops table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'crops'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        echo json_encode(['success' => false, 'error' => 'Crops table not found']);
        exit;
    }
    
    // Get table structure for dynamic field mapping
    $columnsResult = $conn->query("SHOW COLUMNS FROM crops");
    $availableColumns = [];
    while ($column = $columnsResult->fetch_assoc()) {
        $availableColumns[] = $column['Field'];
    }
    
    // Build dynamic INSERT query based on available columns
    $insertFields = ['crop_id', 'crop_name', 'category'];
    $placeholders = ['?', '?', '?'];
    $paramValues = [$data['cropId'], $data['cropName'], $data['category']];
    $paramTypes = 'sss';
    
    // Add water_requirements if available and provided
    if (in_array('water_requirements', $availableColumns) && isset($data['waterRequirements'])) {
        $insertFields[] = 'water_requirements';
        $placeholders[] = '?';
        $paramValues[] = $data['waterRequirements'];
        $paramTypes .= 's';
    }
    
    // Add soil_preference if available and provided
    if (in_array('soil_preference', $availableColumns) && isset($data['soilPreference'])) {
        $insertFields[] = 'soil_preference';
        $placeholders[] = '?';
        $paramValues[] = $data['soilPreference'];
        $paramTypes .= 's';
    }
    
    // Build the complete query with ON DUPLICATE KEY UPDATE
    $query = "INSERT INTO crops (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    
    // Add ON DUPLICATE KEY UPDATE clause
    $updateClauses = [];
    for ($i = 1; $i < count($insertFields); $i++) { // Skip crop_id (index 0)
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
