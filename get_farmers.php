<?php
// Ensure JSON output and suppress HTML errors
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

// Database connection
$db_config = include 'config.php';
$conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Fetch all farmers
$result = $conn->query("SELECT farmer_id, farmer_name, contact_number FROM farmers ORDER BY farmer_name ASC");

$farmers = [];
while ($row = $result->fetch_assoc()) {
    $fullName = trim($row['farmer_name'] ?? '');
    $first = $fullName;
    $last = '';
    if (strpos($fullName, ' ') !== false) {
        $parts = preg_split('/\s+/', $fullName, 2);
        $first = $parts[0];
        $last = $parts[1] ?? '';
    }
    $farmers[] = [
        'farmer_id' => $row['farmer_id'],
        'first_name' => $first,
        'last_name' => $last,
        'phone' => $row['contact_number'] ?? ''
    ];
}

echo json_encode($farmers);

$conn->close();
