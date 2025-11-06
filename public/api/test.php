<?php
// Start output buffering to prevent any unwanted output
ob_start();

// Set error reporting to prevent warnings from breaking JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

echo json_encode(['status' => 'success', 'message' => 'API is working']);
?>
