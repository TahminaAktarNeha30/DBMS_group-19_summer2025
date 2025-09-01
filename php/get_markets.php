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
    
    // Check if markets table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'markets'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }
    
    // Check table structure to determine available columns
    $columnsResult = $conn->query("SHOW COLUMNS FROM markets");
    $availableColumns = [];
    while ($column = $columnsResult->fetch_assoc()) {
        $availableColumns[] = $column['Field'];
    }
    
    // Build query based on available columns
    $selectColumns = ['market_id'];
    
    // Handle name column (market_name vs name)
    if (in_array('market_name', $availableColumns)) {
        $selectColumns[] = 'market_name AS name';
    } else if (in_array('name', $availableColumns)) {
        $selectColumns[] = 'name';
    } else {
        $selectColumns[] = 'NULL AS name';
    }
    
    // Handle type column (market_type vs type)
    if (in_array('market_type', $availableColumns)) {
        $selectColumns[] = 'market_type AS type';
    } else if (in_array('type', $availableColumns)) {
        $selectColumns[] = 'type';
    } else {
        $selectColumns[] = 'NULL AS type';
    }
    
    // Add location
    if (in_array('location', $availableColumns)) {
        $selectColumns[] = 'location';
    } else {
        $selectColumns[] = 'NULL AS location';
    }
    
    // Handle contact person column (contact_info vs contact_person)
    if (in_array('contact_info', $availableColumns)) {
        $selectColumns[] = 'contact_info AS contact_person';
    } else if (in_array('contact_person', $availableColumns)) {
        $selectColumns[] = 'contact_person';
    } else {
        $selectColumns[] = 'NULL AS contact_person';
    }
    
    // Add operational hours if available
    if (in_array('operational_hours', $availableColumns)) {
        $selectColumns[] = 'operational_hours';
    } else {
        $selectColumns[] = 'NULL AS operational_hours';
    }
    
    // Add quantity if available
    if (in_array('quantity', $availableColumns)) {
        $selectColumns[] = 'quantity';
    } else {
        $selectColumns[] = '0 AS quantity';
    }
    
    // Determine order by column
    $orderBy = 'market_id';
    if (in_array('market_name', $availableColumns)) {
        $orderBy = 'market_name';
    } else if (in_array('name', $availableColumns)) {
        $orderBy = 'name';
    }
    
    $query = "SELECT " . implode(', ', $selectColumns) . " FROM markets ORDER BY " . $orderBy . " ASC";
    
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $markets = [];
    while ($row = $result->fetch_assoc()) {
        $markets[] = $row;
    }
    
    echo json_encode($markets);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
