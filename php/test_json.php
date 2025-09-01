<?php
// Simple test to ensure JSON output works
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
ini_set('html_errors', 0);

echo json_encode(['test' => 'success', 'message' => 'JSON output working']);
?>