<?php
// Prevent any output before headers
ob_start();

// Set error handling to catch everything
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON content type
header('Content-Type: application/json');

// Custom error handler to return JSON errors
function handleError($errno, $errstr, $errfile, $errline) {
    $error = [
        'success' => false,
        'message' => 'Server error occurred',
        'debug' => [
            'error' => $errstr,
            'file' => $errfile,
            'line' => $errline
        ]
    ];
    echo json_encode($error);
    exit;
}
set_error_handler('handleError');

try {
    // Include all required files
    require_once __DIR__ . '/../../app/helpers/CorsHelper.php';
    require_once __DIR__ . '/../../app/helpers/SessionHelper.php';
    require_once __DIR__ . '/../../app/helpers/Response.php';
    require_once __DIR__ . '/../../app/controllers/AuthController.php';
    require_once __DIR__ . '/../../app/config/Config.php';

    // Handle CORS first
    CorsHelper::handleCors();

    // Initialize session
    SessionHelper::configureSession();

    // Log request details
    error_log("Auth API - Request details:");
    error_log("Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'));
    error_log("Origin: " . ($_SERVER['HTTP_ORIGIN'] ?? 'UNKNOWN'));

    // Debug session information
    error_log("Auth API - Session ID: " . session_id());
    error_log("Auth API - Session status: " . session_status());
    error_log("Auth API - Session user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'));
    error_log("Auth API - All session data: " . print_r($_SESSION, true));
    error_log("Auth API - Cookies received: " . print_r($_COOKIE, true));

    // Log the request details
    error_log("Auth API - Request Method: " . $_SERVER['REQUEST_METHOD']);
    error_log("Auth API - Request Headers: " . print_r(getallheaders(), true));
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    // Get request body for POST requests
    $input = file_get_contents('php://input');
    $requestData = json_decode($input, true);
    
    // Log request for debugging
    error_log("Auth API Request - Action: " . ($_GET['action'] ?? 'none'));
    error_log("Auth API Request - Data: " . $input);
    
    try {
        $controller = new AuthController();
        $action = $_GET['action'] ?? '';

        if (empty($action)) {
            throw new Exception('No action specified');
        }

        switch ($action) {
            case 'register':
                $controller->register();
                break;
            case 'login':
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    throw new Exception('Login requires POST method');
                }
                $controller->login();
                break;
            case 'logout':
                $controller->logout();
                break;
            case 'check':
                $controller->checkAuth();
                break;
            default:
                throw new Exception('Invalid action: ' . $action);
        }
    } catch (Exception $e) {
        error_log("Auth API Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

    // Clear any buffered output
    if (ob_get_level()) ob_end_clean();
} catch (Throwable $e) {
    // Catch any uncaught errors
    error_log("Auth API Fatal Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'A server error occurred',
        'debug' => [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>
