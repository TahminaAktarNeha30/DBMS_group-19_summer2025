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
    
    // Check if spoilage_records table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'spoilage_records'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        echo json_encode([]);
        exit;
    }
    
    // Get table structure for dynamic field mapping
    $columnsResult = $conn->query("SHOW COLUMNS FROM spoilage_records");
    $availableColumns = [];
    while ($column = $columnsResult->fetch_assoc()) {
        $availableColumns[] = $column['Field'];
    }
    
    // Build dynamic query based on available columns
    $selectFields = [];
    $selectFields[] = in_array('record_id', $availableColumns) ? 'record_id' : "'' AS record_id";
    $selectFields[] = in_array('source_type', $availableColumns) ? 'source_type' : "'' AS source_type";
    $selectFields[] = in_array('quantity_lost', $availableColumns) ? 'quantity_lost' : "0 AS quantity_lost";
    $selectFields[] = in_array('reason', $availableColumns) ? 'reason' : "'' AS reason";
    $selectFields[] = in_array('spoilage_date', $availableColumns) ? 'spoilage_date' : "NULL AS spoilage_date";
    $selectFields[] = in_array('recycling_output', $availableColumns) ? 'recycling_output' : "'' AS recycling_output";
    $selectFields[] = in_array('recycling_quantity', $availableColumns) ? 'recycling_quantity' : "0 AS recycling_quantity";
    $selectFields[] = in_array('financial_loss', $availableColumns) ? 'financial_loss' : "0 AS financial_loss";
    
    $query = "SELECT " . implode(', ', $selectFields) . " FROM spoilage_records ORDER BY spoilage_date DESC";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    
    echo json_encode($records);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}

