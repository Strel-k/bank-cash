<?php
// Database setup script
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'app/config/Config.php';

try {
    // Connect to MySQL
    $pdo = new PDO(
        "mysql:host=" . Config::DB_HOST,
        Config::DB_USER,
        Config::DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✓ Connected to MySQL server\n";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . Config::DB_NAME);
    echo "✓ Database '" . Config::DB_NAME . "' created or exists\n";
    
    // Use database
    $pdo->exec("USE " . Config::DB_NAME);
    
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        phone_number VARCHAR(15) UNIQUE NOT NULL,
        email VARCHAR(255) UNIQUE,
        full_name VARCHAR(100) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        pin_hash VARCHAR(255),
        profile_picture VARCHAR(255),
        is_verified BOOLEAN DEFAULT FALSE,
        login_attempts INT DEFAULT 0,
        last_login_attempt TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "✓ Users table created or exists\n";

    // Add login attempt fields to existing users table if they don't exist
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS login_attempts INT DEFAULT 0");
        $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login_attempt TIMESTAMP NULL");
        echo "✓ Login attempt fields added to users table\n";
    } catch (PDOException $e) {
        echo "⚠️ Could not add login attempt fields (might already exist): " . $e->getMessage() . "\n";
    }
    
    // Create wallets table
    $pdo->exec("CREATE TABLE IF NOT EXISTS wallets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNIQUE NOT NULL,
        balance DECIMAL(15,2) DEFAULT 0.00,
        account_number VARCHAR(20) UNIQUE NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "✓ Wallets table created or exists\n";
    
    // Create transactions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_wallet_id INT NOT NULL,
        receiver_wallet_id INT NOT NULL,
        amount DECIMAL(15,2) NOT NULL,
        transaction_type ENUM('send', 'receive', 'topup', 'withdraw') NOT NULL,
        reference_number VARCHAR(50) UNIQUE NOT NULL,
        description TEXT,
        status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
        FOREIGN KEY (receiver_wallet_id) REFERENCES wallets(id) ON DELETE CASCADE
    )");
    echo "✓ Transactions table created or exists\n";
    
    // Create security tokens table
    $pdo->exec("CREATE TABLE IF NOT EXISTS security_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(255) UNIQUE NOT NULL,
        token_type ENUM('login', 'reset', 'verify') NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        is_used BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "✓ Security tokens table created or exists\n";
    
    // Create indexes for performance
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_users_phone ON users(phone_number)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_transactions_sender ON transactions(sender_wallet_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_transactions_receiver ON transactions(receiver_wallet_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_transactions_reference ON transactions(reference_number)");
    echo "✓ Performance indexes created\n";
    
    echo "\n🎉 Database setup complete! You can now test the registration.\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?>
