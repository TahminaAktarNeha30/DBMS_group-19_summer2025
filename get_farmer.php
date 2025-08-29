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

$farmerId = $_GET['id'];

$stmt = $conn->prepare("SELECT farmer_id, farmer_name, contact_number FROM farmers WHERE farmer_id = ?");
$stmt->bind_param("s", $farmerId);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$first = '';
$last = '';
if ($row && isset($row['farmer_name'])) {
    $fullName = trim($row['farmer_name']);
    if (strpos($fullName, ' ') !== false) {
        $parts = preg_split('/\s+/', $fullName, 2);
        $first = $parts[0];
        $last = $parts[1] ?? '';
    } else {
        $first = $fullName;
    }
}

echo json_encode([
    'farmer_id' => $row['farmer_id'] ?? '',
    'first_name' => $first,
    'last_name' => $last,
    'phone' => $row['contact_number'] ?? ''
]);

$stmt->close();
$conn->close();
