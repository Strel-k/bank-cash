<?php
require_once __DIR__ . '/../models/Wallet.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/SessionHelper.php';
require_once __DIR__ . '/../config/Config.php';

class WalletController {
    private $walletModel;

    public function __construct() {
        // Ensure session is configured
        SessionHelper::configureSession();

        try {
            $this->walletModel = new Wallet();
        } catch (Exception $e) {
            error_log("Wallet model initialization failed: " . $e->getMessage());
            Response::error('Database connection failed');
        }
    }
    
    private function checkAuth() {
        error_log("WalletController::checkAuth - Starting auth check");
        
        // First try to get user ID directly from session
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            error_log("WalletController::checkAuth - Found user_id in session: " . $userId);
            return $userId;
        }
        
        error_log("WalletController::checkAuth - No active session user_id, checking headers");
        
        // Try to get authorization from headers
        $headers = getallheaders();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
        
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
            error_log("WalletController::checkAuth - Found bearer token: " . $token);
            
            // Validate the session and check if it matches the token
            if (session_id() && session_id() === $token && isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
                error_log("WalletController::checkAuth - Valid token auth for user: " . $userId);
                return $userId;
            }
        }

        error_log("WalletController::checkAuth - Authentication failed");
        Response::unauthorized('Please log in to access your wallet');
        return null;
    }
    
    public function getBalance() {
        try {
            error_log("getBalance called");
            $user_id = $this->checkAuth();
            error_log("User ID: " . $user_id);

            $balance = $this->walletModel->getBalance($user_id);
            error_log("Balance: " . $balance);

            Response::success(['balance' => $balance]);
        } catch (Exception $e) {
            error_log("Error in getBalance: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            Response::error('An error occurred while fetching balance');
        }
    }
    
    public function getWalletInfo() {
        $user_id = $this->checkAuth();
        
        $wallet = $this->walletModel->getWalletByUserId($user_id);
        
        if (!$wallet) {
            Response::error('Wallet not found');
        }
        
        Response::success(['wallet' => $wallet]);
    }
    
    public function transferMoney() {
        $user_id = $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Method not allowed', 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['receiver_phone']) || !isset($data['amount'])) {
            Response::error('Receiver phone number and amount are required');
        }

        $receiver_phone = trim($data['receiver_phone']);
        $amount = floatval($data['amount']);
        $description = isset($data['description']) ? trim($data['description']) : '';

        // Validate amount
        if ($amount <= 0) {
            Response::error('Amount must be greater than 0');
        }

        if ($amount > Config::MAX_TRANSFER_AMOUNT) {
            Response::error('Amount exceeds maximum limit');
        }

        // Check if receiver is not the same as sender
        $sender_wallet = $this->walletModel->getWalletByUserId($user_id);
        if ($sender_wallet['phone_number'] === $receiver_phone) {
            Response::error('Cannot transfer to your own phone number');
        }

        $result = $this->walletModel->transferMoney($user_id, $receiver_phone, $amount, $description);

        if ($result['success']) {
            Response::success([
                'reference_number' => $result['reference_number'],
                'new_balance' => $result['new_balance']
            ], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
    
    public function searchAccount() {
        $user_id = $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            Response::error('Method not allowed', 405);
        }

        $phone_number = isset($_GET['phone']) ? trim($_GET['phone']) : '';

        if (empty($phone_number)) {
            Response::error('Phone number is required');
        }

        $wallet = $this->walletModel->getWalletByPhoneNumber($phone_number);

        if (!$wallet) {
            Response::error('Phone number not found');
        }

        // Don't return own account
        if ($wallet['user_id'] == $user_id) {
            Response::error('Cannot search own phone number');
        }

        Response::success([
            'phone_number' => $wallet['phone_number'],
            'full_name' => $wallet['full_name']
        ]);
    }

    public function searchPhoneNumbers() {
        $user_id = $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            Response::error('Method not allowed', 405);
        }

        $query = isset($_GET['q']) ? trim($_GET['q']) : '';

        if (empty($query)) {
            Response::success(['users' => []]);
        }

        // Search for phone numbers that start with the query
        $users = $this->walletModel->searchPhoneNumbers($query, $user_id);

        Response::success(['users' => $users]);
    }

    public function addMoney() {
        $user_id = $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Method not allowed', 405);
        }

        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('Invalid JSON data provided');
        }

        if (!isset($data['amount'])) {
            Response::error('Amount is required');
        }

        $amount = floatval($data['amount']);

        // Validate amount
        if ($amount <= 0) {
            Response::error('Amount must be greater than 0');
        }

        if ($amount > Config::MAX_ADD_MONEY_AMOUNT) {
            Response::error('Amount exceeds maximum limit');
        }

        $result = $this->walletModel->addMoney($user_id, $amount);

        if ($result['success']) {
            Response::success([
                'reference_number' => $result['reference_number'],
                'new_balance' => $result['new_balance']
            ], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function payBills() {
        $user_id = $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Method not allowed', 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['bill_account']) || !isset($data['amount'])) {
            Response::error('Bill account and amount are required');
        }

        $bill_account = trim($data['bill_account']);
        $amount = floatval($data['amount']);

        // Validate amount
        if ($amount <= 0) {
            Response::error('Amount must be greater than 0');
        }

        if ($amount > Config::MAX_BILL_PAYMENT_AMOUNT) {
            Response::error('Amount exceeds maximum limit');
        }

        $result = $this->walletModel->payBills($user_id, $bill_account, $amount);

        if ($result['success']) {
            Response::success([
                'reference_number' => $result['reference_number'],
                'new_balance' => $result['new_balance']
            ], $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
?>
