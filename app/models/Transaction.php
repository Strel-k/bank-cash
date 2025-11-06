<?php
require_once __DIR__ . '/../config/Database.php';

class Transaction {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function getTransactionHistory($user_id, $limit = 50, $offset = 0) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    t.*,
                    sender.account_number as sender_account,
                    receiver.account_number as receiver_account,
                    sender_user.full_name as sender_name,
                    receiver_user.full_name as receiver_name
                FROM transactions t
                JOIN wallets sender ON t.sender_wallet_id = sender.id
                JOIN wallets receiver ON t.receiver_wallet_id = receiver.id
                JOIN users sender_user ON sender.user_id = sender_user.id
                JOIN users receiver_user ON receiver.user_id = receiver_user.id
                WHERE sender.user_id = ? OR receiver.user_id = ?
                ORDER BY t.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$user_id, $user_id, $limit, $offset]);
            
            return $stmt->fetchAll();
            
        } catch(PDOException $e) {
            return [];
        }
    }
    
    public function getTransactionByReference($reference_number) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    t.*,
                    sender.account_number as sender_account,
                    receiver.account_number as receiver_account,
                    sender_user.full_name as sender_name,
                    receiver_user.full_name as receiver_name
                FROM transactions t
                JOIN wallets sender ON t.sender_wallet_id = sender.id
                JOIN wallets receiver ON t.receiver_wallet_id = receiver.id
                JOIN users sender_user ON sender.user_id = sender_user.id
                JOIN users receiver_user ON receiver.user_id = receiver_user.id
                WHERE t.reference_number = ?
            ");
            $stmt->execute([$reference_number]);
            
            return $stmt->fetch();
            
        } catch(PDOException $e) {
            return null;
        }
    }
    
    public function getTransactionCount($user_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM transactions t
                JOIN wallets sender ON t.sender_wallet_id = sender.id
                JOIN wallets receiver ON t.receiver_wallet_id = receiver.id
                WHERE sender.user_id = ? OR receiver.user_id = ?
            ");
            $stmt->execute([$user_id, $user_id]);
            return $stmt->fetch()['count'];
        } catch(PDOException $e) {
            return 0;
        }
    }

    public function getTransactionStats($user_id) {
        try {
            // Total sent
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(amount), 0) as total_sent
                FROM transactions t
                JOIN wallets w ON t.sender_wallet_id = w.id
                WHERE w.user_id = ? AND t.transaction_type = 'send'
            ");
            $stmt->execute([$user_id]);
            $total_sent = $stmt->fetch()['total_sent'];

            // Total received
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(amount), 0) as total_received
                FROM transactions t
                JOIN wallets w ON t.receiver_wallet_id = w.id
                WHERE w.user_id = ? AND t.transaction_type = 'receive'
            ");
            $stmt->execute([$user_id]);
            $total_received = $stmt->fetch()['total_received'];

            // Transaction count
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as transaction_count
                FROM transactions t
                JOIN wallets sender ON t.sender_wallet_id = sender.id
                JOIN wallets receiver ON t.receiver_wallet_id = receiver.id
                WHERE sender.user_id = ? OR receiver.user_id = ?
            ");
            $stmt->execute([$user_id, $user_id]);
            $transaction_count = $stmt->fetch()['transaction_count'];

            return [
                'total_sent' => $total_sent,
                'total_received' => $total_received,
                'transaction_count' => $transaction_count
            ];

        } catch(PDOException $e) {
            return [
                'total_sent' => 0,
                'total_received' => 0,
                'transaction_count' => 0
            ];
        }
    }
    
    public function searchTransactions($user_id, $search_term, $limit = 20) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    t.*,
                    sender.account_number as sender_account,
                    receiver.account_number as receiver_account,
                    sender_user.full_name as sender_name,
                    receiver_user.full_name as receiver_name
                FROM transactions t
                JOIN wallets sender ON t.sender_wallet_id = sender.id
                JOIN wallets receiver ON t.receiver_wallet_id = receiver.id
                JOIN users sender_user ON sender.user_id = sender_user.id
                JOIN users receiver_user ON receiver.user_id = receiver_user.id
                WHERE (sender.user_id = ? OR receiver.user_id = ?)
                AND (
                    t.reference_number LIKE ? OR
                    sender_user.full_name LIKE ? OR
                    receiver_user.full_name LIKE ? OR
                    t.description LIKE ?
                )
                ORDER BY t.created_at DESC
                LIMIT ?
            ");
            
            $search_pattern = '%' . $search_term . '%';
            $stmt->execute([
                $user_id, 
                $user_id, 
                $search_pattern, 
                $search_pattern, 
                $search_pattern, 
                $search_pattern,
                $limit
            ]);
            
            return $stmt->fetchAll();
            
        } catch(PDOException $e) {
            return [];
        }
    }
}
?>
