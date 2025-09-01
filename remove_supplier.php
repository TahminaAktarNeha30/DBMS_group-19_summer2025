<?php
// Remove supplier_name column from existing table
header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Removing supplier_name column ===\n\n";

try {
    $db_config = include 'php/config.php';
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
    
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    
    echo "✓ Database connected\n";
    
    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'perishable_products'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        echo "Table doesn't exist, creating new table without supplier_name...\n";
        // Create new table structure
        include 'verify_setup.php';
        exit;
    }
    
    // Check if supplier_name column exists
    $columnCheck = $conn->query("SHOW COLUMNS FROM perishable_products LIKE 'supplier_name'");
    if ($columnCheck && $columnCheck->num_rows > 0) {
        echo "Removing supplier_name column...\n";
        
        // Drop index if exists
        $conn->query("DROP INDEX IF EXISTS idx_perishable_supplier ON perishable_products");
        
        // Drop column
        if ($conn->query("ALTER TABLE perishable_products DROP COLUMN supplier_name")) {
            echo "✓ supplier_name column removed successfully\n";
        } else {
            throw new Exception('Failed to remove column: ' . $conn->error);
        }
    } else {
        echo "✓ supplier_name column already removed\n";
    }
    
    // Show final table structure
    echo "\nFinal table structure:\n";
    $result = $conn->query("DESCRIBE perishable_products");
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['Field']}: {$row['Type']} " . ($row['Null'] == 'NO' ? 'NOT NULL' : 'NULL') . "\n";
    }
    
    echo "\n=== SUCCESS: supplier_name removed! ===\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}
?>