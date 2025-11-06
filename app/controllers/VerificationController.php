<?php
require_once __DIR__ . '/../models/Verification.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/SessionHelper.php';

class VerificationController {
    private $verificationModel;
    
    public function __construct() {
        $this->verificationModel = new Verification();
    }
    
    private function checkAuth() {
        $userId = SessionHelper::getCurrentUserId();
        if (!$userId) {
            Response::unauthorized('Authentication required');
        }
        return $userId;
    }
    
    public function startVerification() {
        $userId = $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Method not allowed', 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['document_type']) || !isset($data['document_number'])) {
            Response::error('Document type and number are required');
        }
        
        $validTypes = ['passport', 'drivers_license', 'national_id', 'other'];
        if (!in_array($data['document_type'], $validTypes)) {
            Response::error('Invalid document type');
        }
        
        // Check if user already has pending verification
        $existing = $this->verificationModel->getVerificationStatus($userId);
        if ($existing && $existing['verification_status'] === 'pending') {
            Response::error('Verification already in progress');
        }
        
        $result = $this->verificationModel->createVerificationRequest(
            $userId,
            $data['document_type'],
            $data['document_number']
        );
        
        if ($result['success']) {
            $this->verificationModel->logVerificationAction(
                $userId,
                'document_upload',
                ['document_type' => $data['document_type']]
            );
            
            Response::success([
                'verification_id' => $result['verification_id']
            ], 'Verification started successfully');
        } else {
            Response::error($result['message']);
        }
    }
    
    public function uploadDocument() {
        $userId = $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Method not allowed', 405);
        }
        
        if (!isset($_FILES['document'])) {
            Response::error('Document is required');
        }
        
        // Check if verification_id is provided
        $verificationId = $_POST['verification_id'] ?? null;
        
        // Debug logging
        error_log("Upload Document Debug:");
        error_log("POST data: " . json_encode($_POST));
        error_log("FILES data: " . json_encode(array_keys($_FILES)));
        error_log("Verification ID: " . ($verificationId ?: 'NULL'));
        error_log("User ID: " . $userId);
        
        if (!$verificationId) {
            Response::error('Verification ID is required');
        }
        $side = $_POST['side'] ?? 'front';
        
        // Validate file
        $file = $_FILES['document'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            Response::error('Invalid file type. Only JPG, PNG allowed');
        }
        
        if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            Response::error('File too large. Max 5MB');
        }
        
        // Create upload directory
        $uploadDir = __DIR__ . '/../../uploads/verification/' . $userId . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $side . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $result = $this->verificationModel->uploadDocument(
                $verificationId,
                $file['type'],
                $filename,
                $side
            );
            
            if ($result['success']) {
                $this->verificationModel->logVerificationAction(
                    $userId,
                    'document_upload',
                    ['side' => $side, 'filename' => $filename]
                );
                
                Response::success(['message' => 'Document uploaded successfully']);
            } else {
                Response::error($result['message']);
            }
        } else {
            Response::error('Failed to upload document');
        }
    }
    
    public function uploadFaceImage() {
        $userId = $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Method not allowed', 405);
        }
        
        if (!isset($_FILES['face_image']) || !isset($_POST['verification_id'])) {
            Response::error('Face image and verification ID are required');
        }
        
        $verificationId = $_POST['verification_id'];
        
        // Validate file
        $file = $_FILES['face_image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            Response::error('Invalid file type. Only JPG, PNG allowed');
        }
        
        if ($file['size'] > 3 * 1024 * 1024) { // 3MB limit
            Response::error('File too large. Max 3MB');
        }
        
        // Create upload directory
        $uploadDir = __DIR__ . '/../../uploads/verification/' . $userId . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'face_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Simulate face encoding (in production, use actual face recognition API)
            $faceEncoding = json_encode(['encoding' => 'sample_face_encoding_' . time()]);
            
            $result = $this->verificationModel->uploadFaceImage(
                $verificationId,
                $filename,
                $faceEncoding
            );
            
            if ($result['success']) {
                $this->verificationModel->logVerificationAction(
                    $userId,
                    'face_capture',
                    ['filename' => $filename]
                );
                
                Response::success(['message' => 'Face image uploaded successfully']);
            } else {
                Response::error($result['message']);
            }
        } else {
            Response::error('Failed to upload face image');
        }
    }
    
    public function performVerification() {
        $userId = $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Method not allowed', 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['verification_id'])) {
            Response::error('Verification ID is required');
        }
        
        $result = $this->verificationModel->performVerification($data['verification_id']);
        
        if ($result['success']) {
            $this->verificationModel->logVerificationAction(
                $userId,
                $result['status'] === 'verified' ? 'verification_success' : 'verification_failure',
                [
                    'similarity_score' => $result['similarity_score'],
                    'liveness_score' => $result['liveness_score']
                ]
            );
            
            Response::success($result);
        } else {
            Response::error($result['message']);
        }
    }
    
    public function getVerificationStatus() {
        $userId = $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            Response::error('Method not allowed', 405);
        }
        
        $status = $this->verificationModel->getVerificationStatus($userId);
        
        if ($status) {
            Response::success(['verification' => $status]);
        } else {
            Response::success(['verification' => null]);
        }
    }
}
?>
