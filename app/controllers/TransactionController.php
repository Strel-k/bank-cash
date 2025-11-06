<?php
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/SessionHelper.php';

class TransactionController {
    private $transactionModel;
    
    public function __construct() {
        $this->transactionModel = new Transaction();
    }
    
    private function checkAuth() {
        error_log("TransactionController::checkAuth - Starting auth check");
        
        // First try to get user ID directly from session
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            error_log("TransactionController::checkAuth - Found user_id in session: " . $userId);
            return $userId;
        }
        
        error_log("TransactionController::checkAuth - No active session user_id, checking headers");
        
        // Try to get authorization from headers
        $headers = getallheaders();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
        
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
            error_log("TransactionController::checkAuth - Found bearer token: " . $token);
            
            // Validate the session and check if it matches the token
            if (session_id() && session_id() === $token && isset($_SESSION['user_id'])) {
                $userId = $_SESSION['user_id'];
                error_log("TransactionController::checkAuth - Valid token auth for user: " . $userId);
                return $userId;
            }
        }

        error_log("TransactionController::checkAuth - Authentication failed");
        Response::unauthorized('Please log in to view your transactions');
        return null;
    }
    
    public function getHistory() {
        $user_id = $this->checkAuth();

        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

        $transactions = $this->transactionModel->getTransactionHistory($user_id, $limit, $offset);

        // Get total count for pagination
        $total = $this->transactionModel->getTransactionCount($user_id);

        Response::success([
            'transactions' => $transactions,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }
    
    public function getStats() {
        $user_id = $this->checkAuth();
        
        $stats = $this->transactionModel->getTransactionStats($user_id);
        
        Response::success(['stats' => $stats]);
    }
    
    public function searchTransactions() {
        $user_id = $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            Response::error('Method not allowed', 405);
        }
        
        $search_term = isset($_GET['q']) ? trim($_GET['q']) : '';
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
        
        if (empty($search_term)) {
            Response::error('Search term is required');
        }
        
        $transactions = $this->transactionModel->searchTransactions($user_id, $search_term, $limit);
        
        Response::success(['transactions' => $transactions]);
    }
    
    public function getTransactionByReference() {
        $user_id = $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            Response::error('Method not allowed', 405);
        }
        
        $reference = isset($_GET['reference']) ? trim($_GET['reference']) : '';
        
        if (empty($reference)) {
            Response::error('Reference number is required');
        }
        
        $transaction = $this->transactionModel->getTransactionByReference($reference);
        
        if (!$transaction) {
            Response::error('Transaction not found');
        }
        
        // Verify user owns this transaction
        $wallet = new Wallet();
        $user_wallet = $wallet->getWalletByUserId($user_id);
        
        if ($transaction['sender_wallet_id'] != $user_wallet['id'] && 
            $transaction['receiver_wallet_id'] != $user_wallet['id']) {
            Response::error('Access denied');
        }
        
        Response::success(['transaction' => $transaction]);
    }
}
?>
