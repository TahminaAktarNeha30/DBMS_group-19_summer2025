<?php
// Direct table creation script
header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Creating Perishable Products Table ===\n\n";

try {
    // Database connection
    $db_config = include 'php/config.php';
    if (!$db_config) {
        throw new Exception('Config file not found');
    }
    
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
    
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    
    echo "✓ Database connection successful\n";
    
    // Check if table exists first
    $tableCheck = $conn->query("SHOW TABLES LIKE 'perishable_products'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        echo "✓ Table already exists\n";
    } else {
        echo "Creating perishable_products table...\n";
        
        // Create table
        $createTableSQL = "CREATE TABLE IF NOT EXISTS perishable_products (
            product_id VARCHAR(50) PRIMARY KEY,
            product_name VARCHAR(100) NOT NULL,
            category VARCHAR(50) NOT NULL,
            shelf_life_days INT NOT NULL,
            packaging_type VARCHAR(50) NOT NULL,
            quantity DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CHECK (shelf_life_days > 0),
            CHECK (quantity > 0)
        )";
        
        if ($conn->query($createTableSQL)) {
            echo "✓ Table created successfully\n";
        } else {
            throw new Exception('Failed to create table: ' . $conn->error);
        }
        
        // Insert sample data
        echo "Inserting sample data...\n";
        
        $sampleData = [
            ['PROD001', 'Organic Tomatoes', 'Vegetables', 7, 'Plastic Crates', 5.0],
            ['PROD002', 'Fresh Strawberries', 'Fruits', 3, 'Plastic Containers', 2.5],
            ['PROD003', 'Leafy Spinach', 'Leafy Greens', 5, 'Plastic Bags', 1.5]
        ];
        
        $insertSQL = "INSERT INTO perishable_products (product_id, product_name, category, shelf_life_days, packaging_type, quantity) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertSQL);
        
        $insertCount = 0;
        foreach ($sampleData as $data) {
            $stmt->bind_param("sssiss", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5]);
            if ($stmt->execute()) {
                $insertCount++;
            }
        }
        
        echo "✓ Inserted $insertCount sample records\n";
    }
    
    // Verify final state
    $countResult = $conn->query("SELECT COUNT(*) as count FROM perishable_products");
    $count = $countResult->fetch_assoc()['count'];
    echo "✓ Table now has $count total records\n";
    
    echo "\n=== SUCCESS: Database setup complete! ===\n";
    echo "You can now use the Perishable Products module.\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>