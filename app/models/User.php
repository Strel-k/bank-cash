<?php
require_once __DIR__ . '/../config/Database.php';

class User {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        
        if (!$this->db) {
            throw new Exception("Database connection failed");
        }
    }
    
    public function register($phone_number, $email, $full_name, $password, $birthdate = null, $address = null, $gender = null, $pin = null, $id_type = null, $id_front = null, $id_back = null, $face_image = null, $registration_step = 1) {
        try {
            // Debugging: Log registration attempt
            error_log("Registration attempt: phone=$phone_number, email=$email, name=$full_name");
            
            // Check if user already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE phone_number = ? OR email = ?");
            $stmt->execute([$phone_number, $email]);
            
            if ($stmt->rowCount() > 0) {
                error_log("User already exists: phone=$phone_number, email=$email");
                return ['success' => false, 'message' => 'User already exists'];
            }
            
            // Hash password
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            
            // Generate unique account number
            $account_number = 'BC' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            
            // Hash PIN if provided
            $pin_hash = $pin ? password_hash($pin, PASSWORD_BCRYPT) : null;

            // Insert user with additional fields
            $stmt = $this->db->prepare("
                INSERT INTO users (
                    phone_number, email, full_name, birthdate, address, gender, 
                    password_hash, pin, id_type, id_front, id_back, face_image, 
                    registration_step
                ) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            // Debugging: Log the values being inserted
            error_log("Inserting user: phone=$phone_number, email=$email, name=$full_name, birthdate=$birthdate, address=$address, gender=$gender, id_type=$id_type, registration_step=$registration_step");
            
            $stmt->execute([
                $phone_number, $email, $full_name, $birthdate, $address, $gender,
                $password_hash, $pin_hash, $id_type, $id_front, $id_back, $face_image,
                $registration_step
            ]);
            
            $user_id = $this->db->lastInsertId();
            
            // Create wallet for user
            $stmt = $this->db->prepare("
                INSERT INTO wallets (user_id, account_number) 
                VALUES (?, ?)
            ");
            $stmt->execute([$user_id, $account_number]);
            
            error_log("Registration successful: user_id=$user_id, account_number=$account_number");
            return ['success' => true, 'message' => 'Registration successful', 'user_id' => $user_id];
            
        } catch(PDOException $e) {
            error_log("Registration failed with PDO error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        } catch(Exception $e) {
            error_log("Registration failed with general error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }
    
    public function login($phone_number, $password) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, phone_number, email, password_hash, full_name, is_verified, is_admin, login_attempts, last_login_attempt
                FROM users
                WHERE phone_number = ?
            ");
            $stmt->execute([$phone_number]);

            if ($stmt->rowCount() === 1) {
                $user = $stmt->fetch();

                // Check rate limiting
                if ($this->isRateLimited($user)) {
                    return [
                        'success' => false,
                        'message' => 'Too many failed attempts. Please try again later.',
                        'rate_limited' => true
                    ];
                }

                if (password_verify($password, $user['password_hash'])) {
                    // Successful login - reset attempts
                    $this->resetLoginAttempts($user['id']);
                    
                    return [
                        'success' => true,
                        'user' => [
                            'id' => $user['id'],
                            'phone_number' => $user['phone_number'],
                            'email' => $user['email'],
                            'full_name' => $user['full_name'],
                            'is_verified' => $user['is_verified'],
                            'is_admin' => (bool)$user['is_admin']
                        ],
                        'redirect' => (bool)$user['is_admin'] ? '/admin/dashboard' : '/dashboard'
                    ];
                } else {
                    // Failed login - increment attempts
                    $this->incrementLoginAttempts($user['id']);
                }
            }

            return ['success' => false, 'message' => 'Invalid credentials'];

        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }

    private function isRateLimited($user) {
        if ($user['login_attempts'] >= 3) {
            if ($user['last_login_attempt']) {
                $lastAttempt = strtotime($user['last_login_attempt']);
                $now = time();
                $timeDiff = $now - $lastAttempt;

                if ($timeDiff < 30) { // 30 seconds
                    return true;
                } else {
                    // Reset attempts after cooldown
                    $this->resetLoginAttempts($user['id']);
                    return false;
                }
            }
        }
        return false;
    }

    private function incrementLoginAttempts($userId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE users
                SET login_attempts = login_attempts + 1,
                    last_login_attempt = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
        } catch(PDOException $e) {
            // Log error but don't fail the login process
            error_log("Failed to increment login attempts: " . $e->getMessage());
        }
    }

    private function resetLoginAttempts($userId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE users
                SET login_attempts = 0,
                    last_login_attempt = NULL
                WHERE id = ?
            ");
            $stmt->execute([$userId]);
        } catch(PDOException $e) {
            // Log error but don't fail the login process
            error_log("Failed to reset login attempts: " . $e->getMessage());
        }
    }
    
    public function getUserById($user_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, phone_number, email, full_name, profile_picture, is_verified, created_at
                FROM users 
                WHERE id = ?
            ");
            $stmt->execute([$user_id]);
            
            return $stmt->fetch();
            
        } catch(PDOException $e) {
            return null;
        }
    }
    
    public function updateProfile($user_id, $data) {
        try {
            $fields = [];
            $values = [];
            
            if (isset($data['full_name'])) {
                $fields[] = "full_name = ?";
                $values[] = $data['full_name'];
            }
            
            if (isset($data['email'])) {
                $fields[] = "email = ?";
                $values[] = $data['email'];
            }
            
            if (isset($data['profile_picture'])) {
                $fields[] = "profile_picture = ?";
                $values[] = $data['profile_picture'];
            }
            
            if (empty($fields)) {
                return ['success' => false, 'message' => 'No data to update'];
            }
            
            $values[] = $user_id;
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            return ['success' => true, 'message' => 'Profile updated successfully'];
            
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Update failed: ' . $e->getMessage()];
        }
    }

    public function updateRegistrationStep($user_id, $step, $data) {
        try {
            $fields = ["registration_step = ?"];
            $values = [$step];
            
            foreach ($data as $key => $value) {
                if ($value !== null) {
                    $fields[] = "$key = ?";
                    $values[] = $value;
                }
            }
            
            if ($data['pin'] ?? null) {
                $fields[] = "pin = ?";
                $values[] = password_hash($data['pin'], PASSWORD_BCRYPT);
            }
            
            $values[] = $user_id;
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            return [
                'success' => true,
                'message' => 'Registration step updated successfully',
                'user_id' => $user_id,
                'step' => $step
            ];
            
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Update failed: ' . $e->getMessage()];
        }
    }
}
?>
