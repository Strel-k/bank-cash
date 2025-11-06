<?php
// Start output buffering to prevent any unwanted output
ob_start();

// Set error reporting to prevent warnings from breaking JSON
error_reporting(0);
ini_set('display_errors', 0);

require_once '../../app/controllers/VerificationController.php';
require_once '../../app/helpers/Response.php';

// Set JSON headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $controller = new VerificationController();
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'start':
            $controller->startVerification();
            break;
        case 'upload-document':
            $controller->uploadDocument();
            break;
        case 'upload-face':
            $controller->uploadFaceImage();
            break;
        case 'verify':
            $controller->performVerification();
            break;
        case 'status':
            $controller->getVerificationStatus();
            break;
        default:
            Response::error('Invalid action');
    }
} catch (Exception $e) {
    Response::error('An unexpected error occurred. Please try again.');
}
?>
