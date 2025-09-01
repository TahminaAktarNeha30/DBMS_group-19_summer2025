<?php
// Setup database tables for Perishable Products Module
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting database setup...\n\n";

// Include database configuration
$db_config = include 'php/config.php';

// Create connection
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

echo "✓ Connected to database successfully\n";

// Read and execute SQL schema
$sqlFile = 'perishable_products_ultra_simplified_schema.sql';
if (!file_exists($sqlFile)) {
    die("✗ SQL file not found: $sqlFile\n");
}

$sql = file_get_contents($sqlFile);

// Remove the USE database statement since we're already connected
$sql = preg_replace('/USE\s+\w+;\s*/', '', $sql);

// Split into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

$successCount = 0;
$errorCount = 0;

foreach ($statements as $statement) {
    if (empty($statement)) continue;
    
    echo "Executing: " . substr($statement, 0, 50) . "...\n";
    
    if ($conn->query($statement)) {
        $successCount++;
        echo "✓ Success\n";
    } else {
        $errorCount++;
        echo "✗ Error: " . $conn->error . "\n";
    }
}

echo "\n=== Summary ===\n";
echo "Successful statements: $successCount\n";
echo "Failed statements: $errorCount\n";

// Verify table creation
$result = $conn->query("SHOW TABLES LIKE 'perishable_products'");
if ($result && $result->num_rows > 0) {
    echo "✓ perishable_products table exists\n";
    
    // Count records
    $countResult = $conn->query("SELECT COUNT(*) as count FROM perishable_products");
    $count = $countResult->fetch_assoc()['count'];
    echo "✓ Table has $count records\n";
} else {
    echo "✗ perishable_products table was not created\n";
}

$conn->close();
echo "\nDatabase setup completed!\n";
?>