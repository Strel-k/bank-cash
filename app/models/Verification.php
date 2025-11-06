<?php
require_once __DIR__ . '/../config/Database.php';

class Verification {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }
    
    public function createVerificationRequest($userId, $documentType, $documentNumber) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_verification (user_id, id_document_type, id_document_number) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$userId, $documentType, $documentNumber]);
            
            return ['success' => true, 'verification_id' => $this->db->lastInsertId()];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function uploadDocument($verificationId, $documentType, $filePath, $side = 'front') {
        try {
            $column = $side === 'front' ? 'id_document_front_path' : 'id_document_back_path';
            $stmt = $this->db->prepare("
                UPDATE user_verification 
                SET {$column} = ? 
                WHERE id = ?
            ");
            $stmt->execute([$filePath, $verificationId]);
            
            return ['success' => true];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function uploadFaceImage($verificationId, $filePath, $faceEncoding = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE user_verification 
                SET face_image_path = ?, face_encoding = ? 
                WHERE id = ?
            ");
            $stmt->execute([$filePath, $faceEncoding, $verificationId]);
            
            return ['success' => true];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function performVerification($verificationId) {
        try {
            // Get verification data
            $stmt = $this->db->prepare("
                SELECT * FROM user_verification 
                WHERE id = ?
            ");
            $stmt->execute([$verificationId]);
            $verification = $stmt->fetch();
            
            if (!$verification) {
                return ['success' => false, 'message' => 'Verification not found'];
            }
            
            // Simulate API calls for ID verification and facial recognition
            $idVerificationResult = $this->verifyIDDocument($verification);
            $faceVerificationResult = $this->verifyFace($verification);
            
            // Calculate similarity score
            $similarityScore = $this->calculateSimilarity($idVerificationResult, $faceVerificationResult);
            $livenessScore = $faceVerificationResult['liveness_score'] ?? 0.85;
            
            // Update verification record
            $stmt = $this->db->prepare("
                UPDATE user_verification 
                SET 
                    verification_status = ?,
                    similarity_score = ?,
                    liveness_score = ?,
                    verified_at = NOW(),
                    verification_attempts = verification_attempts + 1
                WHERE id = ?
            ");
            
            $status = ($similarityScore >= 0.7 && $livenessScore >= 0.8) ? 'verified' : 'rejected';
            $stmt->execute([$status, $similarityScore, $livenessScore, $verificationId]);
            
            // Update user verification status
            if ($status === 'verified') {
                $stmt = $this->db->prepare("
                    UPDATE users 
                    SET is_verified = TRUE, 
                        verification_level = 'verified',
                        verification_expires_at = DATE_ADD(NOW(), INTERVAL 1 YEAR)
                    WHERE id = ?
                ");
                $stmt->execute([$verification['user_id']]);
            }
            
            return [
                'success' => true,
                'status' => $status,
                'similarity_score' => $similarityScore,
                'liveness_score' => $livenessScore
            ];
            
        } catch(PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function verifyIDDocument($verification) {
        // Simulate ID verification API call
        // In production, integrate with services like:
        // - Jumio
        // - Onfido
        // - AWS Rekognition
        // - Azure Face API
        
        return [
            'document_valid' => true,
            'extracted_data' => [
                'name' => 'Sample User',
                'document_number' => $verification['id_document_number'],
                'expiry_date' => '2025-12-31'
            ],
            'confidence_score' => 0.92
        ];
    }
    
    private function verifyFace($verification) {
        // Simulate facial recognition API call
        // In production, integrate with:
        // - AWS Rekognition
        // - Azure Face API
        // - Google Vision API
        
        return [
            'face_detected' => true,
            'liveness_score' => 0.88,
            'face_encoding' => json_encode(['encoding' => 'sample_encoding_data'])
        ];
    }
    
    private function calculateSimilarity($idResult, $faceResult) {
        // Calculate similarity between ID photo and selfie
        // This is a simplified simulation
        return 0.85; // 85% similarity
    }
    
    public function getVerificationStatus($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM user_verification 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            return null;
        }
    }
    
    public function logVerificationAction($userId, $action, $metadata = []) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO verification_logs (user_id, action, metadata, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $action,
                json_encode($metadata),
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
            
            return ['success' => true];
        } catch(PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>
