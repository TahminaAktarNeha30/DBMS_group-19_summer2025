<?php
// Simple database connection test
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing database connection...\n";

try {
    $db_config = include 'config.php';
    echo "Config loaded: " . json_encode($db_config) . "\n";
    
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
    
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    
    echo "Database connected successfully!\n";
    
    // Check if database exists
    $dbCheck = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'agri_supply_chain'");
    if ($dbCheck->num_rows == 0) {
        echo "Database 'agri_supply_chain' does not exist!\n";
    } else {
        echo "Database 'agri_supply_chain' exists.\n";
    }
    
    // List all tables
    $result = $conn->query("SHOW TABLES");
    echo "Tables in database:\n";
    while ($row = $result->fetch_array()) {
        echo "- " . $row[0] . "\n";
    }
    
    $conn->close();
    echo "Test completed successfully!";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>