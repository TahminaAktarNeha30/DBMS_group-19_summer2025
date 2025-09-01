<?php
// Test database connection and table existence
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "✓ Database connection successful\n";

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'perishable_products'");
if ($result && $result->num_rows > 0) {
    echo "✓ perishable_products table exists\n";
    
    // Show table structure
    $structure = $conn->query("DESCRIBE perishable_products");
    echo "\nTable structure:\n";
    while ($row = $structure->fetch_assoc()) {
        echo "- {$row['Field']}: {$row['Type']} " . ($row['Null'] == 'NO' ? 'NOT NULL' : 'NULL') . "\n";
    }
} else {
    echo "✗ perishable_products table does not exist\n";
    echo "Creating table...\n";
    
    $createTable = "CREATE TABLE IF NOT EXISTS perishable_products (
        product_id VARCHAR(50) PRIMARY KEY,
        product_name VARCHAR(100) NOT NULL,
        category VARCHAR(50) NOT NULL,
        shelf_life_days INT NOT NULL,
        packaging_type VARCHAR(50) NOT NULL,
        quantity DECIMAL(10,2) NOT NULL,
        supplier_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CHECK (shelf_life_days > 0),
        CHECK (quantity > 0)
    )";
    
    if ($conn->query($createTable)) {
        echo "✓ Table created successfully\n";
    } else {
        echo "✗ Error creating table: " . $conn->error . "\n";
    }
}

$conn->close();
?>