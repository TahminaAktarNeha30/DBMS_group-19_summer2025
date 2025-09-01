<?php
// Ensure JSON output and suppress HTML errors
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {

// Database connection
$db_config = include 'config.php';
if (!$db_config) {
    throw new Exception('Config file not found or invalid');
}

$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    throw new Exception('Connection failed: ' . $conn->connect_error);
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception('Invalid JSON data: ' . json_last_error_msg());
}

// Validate input - check required fields
if (!$data || !isset($data['productId']) || !isset($data['productName']) || !isset($data['category']) || 
    !isset($data['shelfLifeDays']) || !isset($data['packagingType']) || !isset($data['quantity'])) {
    throw new Exception('Missing required fields');
}

// Data validation
if (intval($data['shelfLifeDays']) <= 0) {
    throw new Exception('Shelf life must be greater than 0 days');
}

if (floatval($data['quantity']) <= 0) {
    throw new Exception('Quantity must be greater than 0');
}

// Check if table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'perishable_products'");
if (!$tableCheck || $tableCheck->num_rows == 0) {
    throw new Exception('Perishable products table does not exist. Please run the database setup.');
}

// Prepare and execute query
$stmt = $conn->prepare("INSERT INTO perishable_products (
    product_id, product_name, category, 
    shelf_life_days,
    packaging_type, quantity
) VALUES (?, ?, ?, ?, ?, ?)
ON DUPLICATE KEY UPDATE 
    product_name = VALUES(product_name),
    category = VALUES(category),
    shelf_life_days = VALUES(shelf_life_days),
    packaging_type = VALUES(packaging_type),
    quantity = VALUES(quantity),
    updated_at = CURRENT_TIMESTAMP");

if (!$stmt) {
    throw new Exception('Prepare failed: ' . $conn->error);
}

$stmt->bind_param("sssiss", 
    $data['productId'],
    $data['productName'],
    $data['category'],
    $data['shelfLifeDays'],
    $data['packagingType'],
    $data['quantity']
);

if (!$stmt->execute()) {
    throw new Exception('Execute failed: ' . $stmt->error);
}

echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($stmt) && $stmt) {
        $stmt->close();
    }
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>