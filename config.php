<?php
// Disable error reporting in output
error_reporting(0);
ini_set('display_errors', 0);

// Database configuration
$host = 'localhost';
$dbname = 'agri_supply_chain';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

// DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// PDO options for error handling and data fetching
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    // Create PDO instance
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}
