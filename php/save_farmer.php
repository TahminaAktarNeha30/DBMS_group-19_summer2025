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
    
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate data - match frontend form fields
    if (!isset($data['farmerId']) || !isset($data['firstName']) || 
        !isset($data['lastName']) || !isset($data['phone'])) {
        throw new Exception('Missing required fields');
    }
    
    // Validate phone length (11 digits)
    if (!preg_match('/^[0-9]{11}$/', $data['phone'] ?? '')) {
        throw new Exception('Phone must be 11 digits');
    }
    
    // Combine first and last name for farmer_name field
    $farmerName = trim(($data['firstName'] ?? '') . ' ' . ($data['lastName'] ?? ''));
    
    // Prepare and execute query matching actual database schema
    $stmt = $conn->prepare("INSERT INTO farmers (farmer_id, farmer_name, contact_number, address) 
                           VALUES (?, ?, ?, '') 
                           ON DUPLICATE KEY UPDATE 
                           farmer_name = VALUES(farmer_name), contact_number = VALUES(contact_number)");
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param("sss", 
        $data['farmerId'], 
        $farmerName,
        $data['phone']
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }
    
    echo json_encode(['success' => true, 'message' => 'Farmer saved successfully']);
    
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
