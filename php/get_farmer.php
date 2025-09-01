<?php
// Ensure JSON output and suppress HTML errors
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
ini_set('html_errors', 0);

try {
    // Database connection
    $db_config = include 'config.php';
    if (!$db_config) {
        throw new Exception('Config file not found');
    }
    
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
    
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    
    $farmerId = $_GET['id'] ?? '';
    if (empty($farmerId)) {
        throw new Exception('Farmer ID is required');
    }
    
    // Use correct column names matching actual database schema
    $stmt = $conn->prepare("SELECT farmer_id, farmer_name, contact_number, address FROM farmers WHERE farmer_id = ?");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $farmerId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if (!$row) {
        throw new Exception('Farmer not found');
    }
    
    // Split farmer_name into first_name and last_name for frontend compatibility
    $nameParts = explode(' ', $row['farmer_name'] ?? '', 2);
    $firstName = $nameParts[0] ?? '';
    $lastName = $nameParts[1] ?? '';
    
    echo json_encode([
        'farmer_id' => $row['farmer_id'] ?? '',
        'first_name' => $firstName,
        'last_name' => $lastName,
        'farmer_name' => $row['farmer_name'] ?? '',
        'phone' => $row['contact_number'] ?? '',
        'contact_number' => $row['contact_number'] ?? '',
        'address' => $row['address'] ?? ''
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
