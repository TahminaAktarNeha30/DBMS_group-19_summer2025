<?php
// Test connection and execute SQL schema
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully to database: " . $db_config['database'] . "\n";

// Read and execute the SQL file
$sql_file = 'perishable_products_ultra_simplified_schema.sql';
if (file_exists($sql_file)) {
    $sql = file_get_contents($sql_file);
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        if ($conn->query($statement)) {
            $success_count++;
            echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
        } else {
            $error_count++;
            echo "✗ Error: " . $conn->error . "\n";
            echo "Statement: " . substr($statement, 0, 100) . "...\n";
        }
    }
    
    echo "\nSummary:\n";
    echo "Successful statements: $success_count\n";
    echo "Failed statements: $error_count\n";
    
    // Check if tables were created
    $result = $conn->query("SHOW TABLES LIKE 'perishable_products'");
    if ($result && $result->num_rows > 0) {
        echo "✓ Perishable products table created successfully\n";
    }
    
} else {
    echo "SQL file not found: $sql_file\n";
}

$conn->close();
echo "\nDatabase setup completed!\n";
?>