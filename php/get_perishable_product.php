<?php
// Ensure JSON output and suppress HTML errors
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Connection failed: ' . $conn->connect_error]));
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Product ID is required']);
    exit;
}

$productId = $_GET['id'];

// Fetch specific perishable product
$stmt = $conn->prepare("SELECT 
    product_id,
    product_name,
    category,
    shelf_life_days,
    packaging_type,
    quantity
FROM perishable_products
WHERE product_id = ?");

$stmt->bind_param("s", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Product not found']);
} else {
    $product = $result->fetch_assoc();
    echo json_encode(['success' => true, 'data' => $product]);
}

$stmt->close();
$conn->close();
?>