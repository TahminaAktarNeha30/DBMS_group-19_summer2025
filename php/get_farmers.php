<?php
// Ensure JSON output and suppress HTML errors
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
error_reporting(0);
ini_set('display_errors', 0);
ini_set('html_errors', 0);

try {
    // Database connection
    $db_config = include 'config.php';
    if (!$db_config) {
        error_log('Database config file not found');
        echo json_encode([]);
        exit;
    }
    
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
    
    if ($conn->connect_error) {
        error_log('Database connection failed: ' . $conn->connect_error);
        echo json_encode([]);
        exit;
    }
    
    // Set charset
    $conn->set_charset('utf8');
    
    // Check if farmers table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'farmers'");
    if (!$tableCheck || $tableCheck->num_rows == 0) {
        error_log('Farmers table does not exist');
        echo json_encode([]);
        exit;
    }
    
    // Fetch all farmers with proper error handling
    $query = "SELECT farmer_id, farmer_name, contact_number, address FROM farmers ORDER BY farmer_name ASC";
    $result = $conn->query($query);
    
    if (!$result) {
        error_log('Query failed: ' . $conn->error);
        echo json_encode([]);
        exit;
    }
    
    $farmers = [];
    while ($row = $result->fetch_assoc()) {
        // Split farmer_name into first_name and last_name for frontend compatibility
        $nameParts = explode(' ', $row['farmer_name'] ?? '', 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';
        
        $farmers[] = [
            'farmer_id' => $row['farmer_id'] ?? '',
            'first_name' => $firstName,
            'last_name' => $lastName,
            'farmer_name' => $row['farmer_name'] ?? '',
            'phone' => $row['contact_number'] ?? '',
            'contact_number' => $row['contact_number'] ?? '',
            'address' => $row['address'] ?? ''
        ];
    }
    
    // Always return a valid JSON array
    echo json_encode($farmers);
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log('Error in get_farmers.php: ' . $e->getMessage());
    // Always return empty array on error to maintain consistency
    echo json_encode([]);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
