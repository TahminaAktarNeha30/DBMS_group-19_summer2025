<?php
// Disable error reporting in output
error_reporting(0);
ini_set('display_errors', 0);

// Set headers
header('Content-Type: application/json; charset=utf-8');

require_once '../config.php';

try {
    // Test database connection
    if (!isset($pdo)) {
        throw new Exception('Database connection not established');
    }

    // Try to query the database
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'tables' => $tables
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
