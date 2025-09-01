<?php
// Quick verification script
header('Content-Type: text/plain');

echo "=== Database Verification ===\n\n";

try {
    $db_config = include 'php/config.php';
    echo "✓ Config loaded\n";
    
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
    
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    
    echo "✓ Database connected\n";
    
    // Try to create table if it doesn't exist
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
        echo "✓ Table created/verified\n";
    } else {
        throw new Exception('Table creation failed: ' . $conn->error);
    }
    
    // Check if we need sample data
    $countResult = $conn->query("SELECT COUNT(*) as count FROM perishable_products");
    $count = $countResult->fetch_assoc()['count'];
    
    if ($count == 0) {
        echo "Adding sample data...\n";
        
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
        
        echo "✓ Added $insertCount sample records\n";
        $count = $insertCount;
    }
    
    echo "✓ Table has $count records\n";
    echo "\n=== SUCCESS: Ready to use! ===\n";
    echo "Go to perishable_products.html to test the module.\n";
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}
?>