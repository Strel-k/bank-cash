-- B-Cash Database Schema
CREATE DATABASE IF NOT EXISTS b_cash;
USE b_cash;

-- Users table
CREATE TABLE users (
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
);

-- Wallets table
CREATE TABLE wallets (
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
CREATE TABLE transactions (
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
CREATE TABLE security_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    token_type ENUM('login', 'reset', 'verify') NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create indexes for performance
CREATE INDEX idx_users_phone ON users(phone_number);
CREATE INDEX idx_transactions_sender ON transactions(sender_wallet_id);
CREATE INDEX idx_transactions_receiver ON transactions(receiver_wallet_id);
CREATE INDEX idx_transactions_reference ON transactions(reference_number);
