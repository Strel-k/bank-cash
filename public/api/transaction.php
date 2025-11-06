<?php
// Start output buffering
ob_start();

// Configure session BEFORE any output
require_once __DIR__ . '/../../app/helpers/SessionHelper.php';
require_once __DIR__ . '/../../app/helpers/Response.php';
require_once __DIR__ . '/../../app/controllers/TransactionController.php';

SessionHelper::configureSession();

// Debug session information
error_log("Transaction API - Request from: " . ($_SERVER['HTTP_ORIGIN'] ?? 'Unknown'));
error_log("Transaction API - Session ID: " . session_id());
error_log("Transaction API - Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));
error_log("Transaction API - Session data: " . print_r($_SESSION, true));

// Load and handle CORS
require_once __DIR__ . '/../../app/helpers/CorsHelper.php';
CorsHelper::handleCors();

// First check authentication
if (!SessionHelper::isAuthenticated()) {
    Response::unauthorized();
}

// Handle CORS headers
$allowedOrigins = [
    'http://localhost',
    'http://localhost:3000',
    'http://127.0.0.1',
    'http://127.0.0.1:3000',
    'http://localhost:80'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? null;
error_log("Transaction API - Request Origin: " . $origin);

// Set JSON and CORS headers
header('Content-Type: application/json');
if ($origin && in_array($origin, $allowedOrigins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
}

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Ensure session cookie is accessible
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', false); // Set to true in production with HTTPS

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $controller = new TransactionController();
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'history':
            $controller->getHistory();
            break;
        case 'stats':
            $controller->getStats();
            break;
        case 'search':
            $controller->searchTransactions();
            break;
        case 'reference':
            $controller->getTransactionByReference();
            break;
        default:
            Response::error('Invalid action');
    }
} catch (Exception $e) {
    Response::error('An unexpected error occurred. Please try again.');
}
?>
