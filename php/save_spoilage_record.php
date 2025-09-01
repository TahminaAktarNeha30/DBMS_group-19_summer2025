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
    if (!isset($data['recordId']) || !isset($data['quantityLost']) || !isset($data['spoilageDate'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields: recordId, quantityLost, spoilageDate']);
        exit;
    }
    
    // Check if spoilage_records table exists and get its structure
    $tableCheck = $conn->query("SHOW TABLES LIKE 'spoilage_records'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        // Create table with comprehensive schema
        $createTable = "CREATE TABLE spoilage_records (
            record_id VARCHAR(50) PRIMARY KEY,
            source_type VARCHAR(100),
            quantity_lost DECIMAL(10,2),
            reason VARCHAR(255),
            spoilage_date DATE,
            recycling_output VARCHAR(100),
            recycling_quantity DECIMAL(10,2),
            financial_loss DECIMAL(12,2),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if (!$conn->query($createTable)) {
            throw new Exception('Failed to create table: ' . $conn->error);
        }
    }
    
    // Get table structure for dynamic field mapping
    $columnsResult = $conn->query("SHOW COLUMNS FROM spoilage_records");
    $availableColumns = [];
    while ($column = $columnsResult->fetch_assoc()) {
        $availableColumns[] = $column['Field'];
    }
    
    // Build dynamic INSERT query based on available columns
    $insertFields = ['record_id', 'quantity_lost', 'spoilage_date'];
    $placeholders = ['?', '?', '?'];
    $paramValues = [$data['recordId'], $data['quantityLost'], $data['spoilageDate']];
    $paramTypes = 'sds';
    
    // Add optional fields if available in database and provided in data
    if (in_array('source_type', $availableColumns) && isset($data['sourceType'])) {
        $insertFields[] = 'source_type';
        $placeholders[] = '?';
        $paramValues[] = $data['sourceType'];
        $paramTypes .= 's';
    }
    
    if (in_array('reason', $availableColumns) && isset($data['reason'])) {
        $insertFields[] = 'reason';
        $placeholders[] = '?';
        $paramValues[] = $data['reason'];
        $paramTypes .= 's';
    }
    
    if (in_array('recycling_output', $availableColumns) && isset($data['recyclingOutput'])) {
        $insertFields[] = 'recycling_output';
        $placeholders[] = '?';
        $paramValues[] = $data['recyclingOutput'];
        $paramTypes .= 's';
    }
    
    if (in_array('recycling_quantity', $availableColumns) && isset($data['recyclingQuantity'])) {
        $insertFields[] = 'recycling_quantity';
        $placeholders[] = '?';
        $paramValues[] = $data['recyclingQuantity'];
        $paramTypes .= 'd';
    }
    
    if (in_array('financial_loss', $availableColumns) && isset($data['financialLoss'])) {
        $insertFields[] = 'financial_loss';
        $placeholders[] = '?';
        $paramValues[] = $data['financialLoss'];
        $paramTypes .= 'd';
    }
    
    // Build the complete query with ON DUPLICATE KEY UPDATE
    $query = "INSERT INTO spoilage_records (" . implode(', ', $insertFields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    
    // Add ON DUPLICATE KEY UPDATE clause
    $updateClauses = [];
    for ($i = 1; $i < count($insertFields); $i++) { // Skip record_id (index 0)
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

