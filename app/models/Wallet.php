<?php
require_once __DIR__ . '/../config/Database.php';

class Wallet {
    private $db;
    

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        if ($this->db === null) {
            throw new Exception('Database connection failed');
        }
    }
    
    public function getWalletByUserId($user_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT w.*, u.full_name 
                FROM wallets w
                JOIN users u ON w.user_id = u.id
                WHERE w.user_id = ?
            ");
            $stmt->execute([$user_id]);
            
            return $stmt->fetch();
            
        } catch(PDOException $e) {
            return null;
        }
    }
    
    public function getBalance($user_id) {
        try {
            if ($this->db === null) {
                throw new Exception('Database connection is null');
            }
            $stmt = $this->db->prepare("
                SELECT balance
                FROM wallets
                WHERE user_id = ?
            ");
            $stmt->execute([$user_id]);

            $result = $stmt->fetch();
            return $result ? $result['balance'] : 0;

        } catch(PDOException $e) {
            error_log("PDO Error in getBalance: " . $e->getMessage());
            return 0;
        } catch(Exception $e) {
            error_log("Exception in getBalance: " . $e->getMessage());
            return 0;
        }
    }
    
    public function updateBalance($user_id, $amount, $operation = 'add', $use_transaction = true) {
        try {
            if ($use_transaction) {
                $this->db->beginTransaction();
            }
            
            // Get current balance
            $current_balance = $this->getBalance($user_id);
            
            // Calculate new balance
            if ($operation === 'add') {
                $new_balance = $current_balance + $amount;
            } else {
                $new_balance = $current_balance - $amount;
                
                // Check if sufficient balance
                if ($new_balance < 0) {
                    if ($use_transaction) {
                        $this->db->rollBack();
                    }
                    return ['success' => false, 'message' => 'Insufficient balance'];
                }
            }
            
            // Update balance
            $stmt = $this->db->prepare("
                UPDATE wallets 
                SET balance = ? 
                WHERE user_id = ?
            ");
            $stmt->execute([$new_balance, $user_id]);
            
            if ($use_transaction) {
                $this->db->commit();
            }
            return ['success' => true, 'new_balance' => $new_balance];
            
        } catch(PDOException $e) {
            if ($use_transaction) {
                $this->db->rollBack();
            }
            return ['success' => false, 'message' => 'Balance update failed: ' . $e->getMessage()];
        }
    }
    
    public function getWalletByAccountNumber($account_number) {
        try {
            $stmt = $this->db->prepare("
                SELECT w.*, u.full_name
                FROM wallets w
                JOIN users u ON w.user_id = u.id
                WHERE w.account_number = ?
            ");
            $stmt->execute([$account_number]);

            return $stmt->fetch();

        } catch(PDOException $e) {
            return null;
        }
    }

    public function getWalletByPhoneNumber($phone_number) {
        try {
            $stmt = $this->db->prepare("
                SELECT w.*, u.full_name
                FROM wallets w
                JOIN users u ON w.user_id = u.id
                WHERE u.phone_number = ?
            ");
            $stmt->execute([$phone_number]);

            return $stmt->fetch();

        } catch(PDOException $e) {
            return null;
        }
    }

    public function searchPhoneNumbers($query, $exclude_user_id = null) {
        try {
            $sql = "
                SELECT u.phone_number, u.full_name
                FROM users u
                JOIN wallets w ON u.id = w.user_id
                WHERE u.phone_number LIKE ?
            ";
            $params = [$query . '%'];

            if ($exclude_user_id) {
                $sql .= " AND u.id != ?";
                $params[] = $exclude_user_id;
            }

            $sql .= " ORDER BY u.phone_number LIMIT 10";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll();

        } catch(PDOException $e) {
            return [];
        }
    }
    
    public function transferMoney($sender_id, $receiver_phone, $amount, $description = '') {
        try {
            $this->db->beginTransaction();

            // Get sender wallet
            $sender_wallet = $this->getWalletByUserId($sender_id);
            if (!$sender_wallet) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Sender wallet not found'];
            }

            // Get receiver wallet
            $receiver_wallet = $this->getWalletByPhoneNumber($receiver_phone);
            if (!$receiver_wallet) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Receiver phone number not found'];
            }

            // Check if sender has sufficient balance
            if ($sender_wallet['balance'] < $amount) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Insufficient balance'];
            }

            // Generate unique reference number using microtime and random component for guaranteed uniqueness
            $reference_number = 'TXN' . str_replace('.', '', microtime(true)) . mt_rand(100, 999);

            // Deduct from sender (without starting a new transaction)
            $this->updateBalance($sender_id, $amount, 'subtract', false);

            // Add to receiver (without starting a new transaction)
            $this->updateBalance($receiver_wallet['user_id'], $amount, 'add', false);

            // Record transaction for sender
            $stmt = $this->db->prepare("
                INSERT INTO transactions
                (sender_wallet_id, receiver_wallet_id, amount, transaction_type, reference_number, description)
                VALUES (?, ?, ?, 'send', ?, ?)
            ");
            $stmt->execute([
                $sender_wallet['id'],
                $receiver_wallet['id'],
                $amount,
                $reference_number,
                $description
            ]);

            // Record transaction for receiver with modified reference number
            $receiver_reference = $reference_number . '-R';
            $stmt = $this->db->prepare("
                INSERT INTO transactions
                (sender_wallet_id, receiver_wallet_id, amount, transaction_type, reference_number, description)
                VALUES (?, ?, ?, 'receive', ?, ?)
            ");
            $stmt->execute([
                $sender_wallet['id'],
                $receiver_wallet['id'],
                $amount,
                $receiver_reference,
                $description
            ]);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Transfer successful',
                'reference_number' => $reference_number,
                'new_balance' => $sender_wallet['balance'] - $amount
            ];

        } catch(PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Transfer failed: ' . $e->getMessage()];
        }
    }

    public function addMoney($user_id, $amount) {
        try {
            $this->db->beginTransaction();

            // Get user wallet
            $wallet = $this->getWalletByUserId($user_id);
            if (!$wallet) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Wallet not found'];
            }

            // Generate unique reference number using microtime for guaranteed uniqueness
            $reference_number = 'ADD' . str_replace('.', '', microtime(true));

            // Add to balance (without starting a new transaction)
            $result = $this->updateBalance($user_id, $amount, 'add', false);
            if (!$result['success']) {
                $this->db->rollBack();
                return $result;
            }

            // Record transaction
            $stmt = $this->db->prepare("
                INSERT INTO transactions
                (sender_wallet_id, receiver_wallet_id, amount, transaction_type, reference_number, description, status)
                VALUES (?, ?, ?, 'topup', ?, 'Money added to wallet', 'completed')
            ");
            $stmt->execute([
                $wallet['id'],
                $wallet['id'],
                $amount,
                $reference_number
            ]);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Money added successfully',
                'reference_number' => $reference_number,
                'new_balance' => $result['new_balance']
            ];

        } catch(PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Add money failed: ' . $e->getMessage()];
        }
    }

    public function payBills($user_id, $bill_account, $amount) {
        try {
            $this->db->beginTransaction();

            // Get user wallet
            $wallet = $this->getWalletByUserId($user_id);
            if (!$wallet) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Wallet not found'];
            }

            // Check if user has sufficient balance
            if ($wallet['balance'] < $amount) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Insufficient balance'];
            }

            // Generate unique reference number using microtime for guaranteed uniqueness
            $reference_number = 'BILL' . str_replace('.', '', microtime(true));

            // Deduct from balance (without starting a new transaction)
            $result = $this->updateBalance($user_id, $amount, 'subtract', false);
            if (!$result['success']) {
                $this->db->rollBack();
                return $result;
            }

            // Record transaction
            $stmt = $this->db->prepare("
                INSERT INTO transactions
                (sender_wallet_id, receiver_wallet_id, amount, transaction_type, reference_number, description, status)
                VALUES (?, ?, ?, 'withdraw', ?, ?, 'completed')
            ");
            $stmt->execute([
                $wallet['id'],
                $wallet['id'], // Use the same wallet ID since it's a withdrawal
                $amount,
                $reference_number,
                "Bill payment to account: $bill_account"
            ]);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Bill payment successful',
                'reference_number' => $reference_number,
                'new_balance' => $result['new_balance']
            ];

        } catch(PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Bill payment failed: ' . $e->getMessage()];
        }
    }
}
?>
