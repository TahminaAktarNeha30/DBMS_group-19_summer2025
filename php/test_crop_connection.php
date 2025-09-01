<?php
// Diagnostic tool for crop management database connection and structure
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

try {
    echo json_encode(['step' => 'Starting crop diagnostic test...', 'status' => 'info']);
    echo "\n";
    
    // Database connection
    $db_config = include 'config.php';
    if (!$db_config) {
        throw new Exception('Config file not found');
    }
    
    echo json_encode(['step' => 'Config loaded successfully', 'config' => array_keys($db_config), 'status' => 'success']);
    echo "\n";
    
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
    
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    
    echo json_encode(['step' => 'Database connection successful', 'database' => $db_config['database'], 'status' => 'success']);
    echo "\n";
    
    // Check if crops table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'crops'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        echo json_encode(['step' => 'crops table does not exist', 'status' => 'error']);
        echo "\n";
        
        // Show all tables
        $allTables = $conn->query("SHOW TABLES");
        $tables = [];
        while ($table = $allTables->fetch_array()) {
            $tables[] = $table[0];
        }
        echo json_encode(['step' => 'Available tables', 'tables' => $tables, 'status' => 'info']);
        echo "\n";
    } else {
        echo json_encode(['step' => 'crops table exists', 'status' => 'success']);
        echo "\n";
        
        // Check table structure
        $columnsResult = $conn->query("SHOW COLUMNS FROM crops");
        $columns = [];
        while ($column = $columnsResult->fetch_assoc()) {
            $columns[] = [
                'field' => $column['Field'],
                'type' => $column['Type'],
                'null' => $column['Null'],
                'key' => $column['Key'],
                'default' => $column['Default']
            ];
        }
        echo json_encode(['step' => 'Table structure', 'columns' => $columns, 'status' => 'success']);
        echo "\n";
        
        // Check sample data
        $sampleData = $conn->query("SELECT * FROM crops LIMIT 3");
        $samples = [];
        while ($row = $sampleData->fetch_assoc()) {
            $samples[] = $row;
        }
        echo json_encode(['step' => 'Sample data', 'samples' => $samples, 'count' => count($samples), 'status' => 'success']);
        echo "\n";
        
        // Check total count
        $countResult = $conn->query("SELECT COUNT(*) as total FROM crops");
        $totalCount = $countResult->fetch_assoc()['total'];
        echo json_encode(['step' => 'Total records', 'total' => $totalCount, 'status' => 'success']);
        echo "\n";
    }
    
    echo json_encode(['step' => 'Crop diagnostic test completed successfully', 'status' => 'success']);
    
} catch (Exception $e) {
    echo json_encode(['step' => 'Error occurred', 'error' => $e->getMessage(), 'status' => 'error']);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>