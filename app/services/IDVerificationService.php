<?php
require_once __DIR__ . '/../config/VerificationConfig.php';

class IDVerificationService {
    private $service;
    private $config;
    
    public function __construct($service = null) {
        $this->service = $service ?? VerificationConfig::ID_VERIFICATION_SERVICE;
        $this->config = VerificationConfig::getServiceConfig($this->service);
    }
    
    /**
     * Verify ID document using configured service
     * @param string $imagePath Path to the ID document image
     * @param string $documentType Type of document (passport, drivers_license, national_id)
     * @param string $documentNumber Document number
     * @return array Verification result
     */
    public function verifyIDDocument($imagePath, $documentType, $documentNumber) {
        switch ($this->service) {
            case 'jumio':
                return $this->verifyWithJumio($imagePath, $documentType, $documentNumber);
            case 'onfido':
                return $this->verifyWithOnfido($imagePath, $documentType, $documentNumber);
            case 'aws_rekognition':
                return $this->verifyWithAWSRekognition($imagePath, $documentType, $documentNumber);
            default:
                return $this->simulateVerification($imagePath, $documentType, $documentNumber);
        }
    }
    
    /**
     * Verify with Jumio (simulated for now)
     */
    private function verifyWithJumio($imagePath, $documentType, $documentNumber) {
        // In production, this would make actual API calls to Jumio
        // For now, return simulated response
        return [
            'success' => true,
            'document_valid' => true,
            'extracted_data' => [
                'name' => 'Sample User',
                'document_number' => $documentNumber,
                'document_type' => $documentType,
                'expiry_date' => '2025-12-31'
            ],
            'confidence_score' => 0.95,
            'verification_id' => uniqid('jumio_'),
            'status' => 'verified'
        ];
    }
    
    /**
     * Verify with Onfido (simulated for now)
     */
    private function verifyWithOnfido($imagePath, $documentType, $documentNumber) {
        // In production, this would make actual API calls to Onfido
        return [
            'success' => true,
            'document_valid' => true,
            'extracted_data' => [
                'name' => 'Sample User',
                'document_number' => $documentNumber,
                'document_type' => $documentType,
                'expiry_date' => '2025-12-31'
            ],
            'confidence_score' => 0.93,
            'verification_id' => uniqid('onfido_'),
            'status' => 'verified'
        ];
    }
    
    /**
     * Verify with AWS Rekognition (simulated for now)
     */
    private function verifyWithAWSRekognition($imagePath, $documentType, $documentNumber) {
        // In production, this would use AWS Rekognition API
        return [
            'success' => true,
            'document_valid' => true,
            'extracted_data' => [
                'name' => 'Sample User',
                'document_number' => $documentNumber,
                'document_type' => $documentType
            ],
            'confidence_score' => 0.90,
            'verification_id' => uniqid('aws_'),
            'status' => 'verified'
        ];
    }
    
    /**
     * Simulate verification for development/testing
     */
    private function simulateVerification($imagePath, $documentType, $documentNumber) {
        return [
            'success' => true,
            'document_valid' => true,
            'extracted_data' => [
                'name' => 'Sample User',
                'document_number' => $documentNumber,
                'document_type' => $documentType,
                'expiry_date' => '2025-12-31'
            ],
            'confidence_score' => 0.85,
            'verification_id' => uniqid('sim_'),
            'status' => 'verified'
        ];
    }
    
    /**
     * Check if service is properly configured
     * @return bool
     */
    public function isConfigured() {
        return VerificationConfig::isServiceConfigured($this->service);
    }
}
?>
