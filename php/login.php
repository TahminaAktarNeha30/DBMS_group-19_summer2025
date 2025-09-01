<?php
session_start();

// Simple fixed-credential auth for demo
$validEmail = 'example@gmail.com';
$validPassword = 'admin1234';

// Map modules to landing pages
$moduleRoutes = [
    // Inventory & Operations
    'inventory_operations' => '../inventory_rotation.html', // change to preferred landing page
    // Agriculture & Supply Chain
    'agriculture_supply_chain' => '../crop_management.html', // change to preferred landing page
    // Transaction & Records
    'transaction_records' => '../harvest_record.html', // change to preferred landing page
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.html');
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? (string)$_POST['password'] : '';
$module = isset($_POST['module']) ? (string)$_POST['module'] : '';

// Validate credentials
if ($email !== $validEmail || $password !== $validPassword || !isset($moduleRoutes[$module])) {
    header('Location: ../login.html?error=1');
    exit;
}

// Minimal session persistence
$_SESSION['user_email'] = $email;
$_SESSION['module'] = $module;

// Prevent caching of protected pages
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Always redirect to dashboard regardless of selected module
header('Location: ../index.html');
exit;
?>


