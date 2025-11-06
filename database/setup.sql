-- B-Cash Complete Database Setup
-- This script creates the complete database structure for B-Cash AJAX

-- Create database
CREATE DATABASE IF NOT EXISTS b_cash_ajax;
USE b_cash_ajax;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone_number VARCHAR(15) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    pin_hash VARCHAR(255),
    profile_picture VARCHAR(255),
    birthdate DATE,
    address TEXT,
    gender ENUM('male', 'female', 'other'),
    is_verified BOOLEAN DEFAULT FALSE,
    verification_level ENUM('basic', 'verified', 'premium') DEFAULT 'basic',
    verification_expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Wallets table
CREATE TABLE IF NOT EXISTS wallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0.00,
    account_number VARCHAR(20) UNIQUE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Transactions table
CREATE TABLE IF NOT EXISTS transactions (
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
);

-- Security tokens table
CREATE TABLE IF NOT EXISTS security_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    token_type ENUM('login', 'reset', 'verify') NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- User verification table for ID documents and facial data
CREATE TABLE IF NOT EXISTS user_verification (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    verification_status ENUM('pending', 'verified', 'rejected', 'expired') DEFAULT 'pending',
    id_document_type ENUM('passport', 'drivers_license', 'national_id', 'other') NOT NULL,
    id_document_number VARCHAR(100) NOT NULL,
    id_document_front_path VARCHAR(255),
    id_document_back_path VARCHAR(255),
    id_document_ocr_data JSON,
    face_encoding TEXT,
    face_image_path VARCHAR(255),
    liveness_score DECIMAL(3,2),
    similarity_score DECIMAL(3,2),
    verification_attempts INT DEFAULT 0,
    last_verification_attempt TIMESTAMP NULL,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_verification_status (verification_status)
);

-- Verification logs for audit trail
CREATE TABLE IF NOT EXISTS verification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action ENUM('document_upload', 'face_capture', 'verification_attempt', 'verification_success', 'verification_failure'),
    metadata JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_users_phone ON users(phone_number);
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_transactions_sender ON transactions(sender_wallet_id);
CREATE INDEX IF NOT EXISTS idx_transactions_receiver ON transactions(receiver_wallet_id);
CREATE INDEX IF NOT EXISTS idx_transactions_reference ON transactions(reference_number);
CREATE INDEX IF NOT EXISTS idx_transactions_created ON transactions(created_at);
CREATE INDEX IF NOT EXISTS idx_wallets_account ON wallets(account_number);
CREATE INDEX IF NOT EXISTS idx_security_tokens_user ON security_tokens(user_id);
CREATE INDEX IF NOT EXISTS idx_security_tokens_token ON security_tokens(token);

-- Insert sample data for testing
INSERT INTO users (phone_number, email, full_name, password_hash, birthdate, address, gender, is_verified) VALUES
('09123456789', 'admin@bcash.com', 'Admin User', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1990-01-01', 'Sample Address', 'male', TRUE),
('09187654321', 'user@bcash.com', 'Test User', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1995-05-15', 'Test Address', 'female', FALSE);

-- Insert sample wallets
INSERT INTO wallets (user_id, balance, account_number) VALUES
(1, 10000.00, 'BC000001'),
(2, 5000.00, 'BC000002');

-- Insert sample transactions
INSERT INTO transactions (sender_wallet_id, receiver_wallet_id, amount, transaction_type, reference_number, description, status) VALUES
(1, 2, 1000.00, 'send', 'TXN001', 'Sample transfer', 'completed'),
(2, 1, 500.00, 'send', 'TXN002', 'Sample transfer back', 'completed');

-- Update wallet balances based on transactions
UPDATE wallets SET balance = balance - 1000.00 WHERE id = 1;
UPDATE wallets SET balance = balance + 1000.00 WHERE id = 2;
UPDATE wallets SET balance = balance - 500.00 WHERE id = 2;
UPDATE wallets SET balance = balance + 500.00 WHERE id = 1;

-- Show final state
SELECT 'Database setup completed successfully!' as status;
SELECT COUNT(*) as user_count FROM users;
SELECT COUNT(*) as wallet_count FROM wallets;
SELECT COUNT(*) as transaction_count FROM transactions; 