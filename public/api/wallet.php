<?php
// Start output buffering FIRST to prevent any unwanted output
ob_start();

// Set error reporting to prevent warnings from breaking JSON
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configure session BEFORE any output
require_once __DIR__ . '/../../app/helpers/SessionHelper.php';
require_once __DIR__ . '/../../app/helpers/Response.php';
require_once __DIR__ . '/../../app/controllers/WalletController.php';

SessionHelper::configureSession();

// Debug session information
error_log("Wallet API - Request from: " . ($_SERVER['HTTP_ORIGIN'] ?? 'Unknown'));
error_log("Wallet API - Session ID: " . session_id());
error_log("Wallet API - Session user_id: " . ($_SESSION['user_id'] ?? 'not set'));
error_log("Wallet API - Session data: " . print_r($_SESSION, true));

// First check authentication
if (!SessionHelper::isAuthenticated()) {
    Response::unauthorized();
}
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', false); // Set to true in production with HTTPS

// Debug session state
error_log("Wallet API - Before processing - Session ID: " . session_id());
error_log("Wallet API - Before processing - User ID: " . ($_SESSION['user_id'] ?? 'not set'));
error_log("Wallet API - Request Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'not set'));

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $controller = new WalletController();
    $action = $_GET['action'] ?? '';

    error_log("Wallet API called with action: " . $action);

    switch ($action) {
        case 'balance':
            $controller->getBalance();
            break;
        case 'info':
            $controller->getWalletInfo();
            break;
        case 'search':
            $controller->searchAccount();
            break;
        case 'searchPhoneNumbers':
            $controller->searchPhoneNumbers();
            break;
        case 'transfer':
            $controller->transferMoney();
            break;
        case 'addMoney':
            $controller->addMoney();
            break;
        case 'payBills':
            $controller->payBills();
            break;
        default:
            Response::error('Invalid action');
    }
} catch (Exception $e) {
    error_log("Exception in wallet.php: " . $e->getMessage());
    Response::error('An unexpected error occurred. Please try again.');
}
?>
