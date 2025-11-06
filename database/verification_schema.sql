-- Add verification tables to existing B-Cash database

-- User verification table for ID documents and facial data
CREATE TABLE user_verification (
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
CREATE TABLE verification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action ENUM('document_upload', 'face_capture', 'verification_attempt', 'verification_success', 'verification_failure'),
    metadata JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add verification fields to users table
ALTER TABLE users 
ADD COLUMN is_verified BOOLEAN DEFAULT FALSE,
ADD COLUMN verification_level ENUM('basic', 'verified', 'premium') DEFAULT 'basic',
ADD COLUMN verification_expires_at TIMESTAMP NULL;
