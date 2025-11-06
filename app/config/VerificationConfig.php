<?php
class VerificationConfig {
    // ID Verification Service Configuration
    const ID_VERIFICATION_SERVICE = 'jumio'; // Options: 'jumio', 'onfido', 'aws_rekognition'
    
    // Jumio Configuration
    const JUMIO_API_URL = 'https://api.jumio.com';
    const JUMIO_ACCOUNT_ID = 'your_jumio_account_id';
    const JUMIO_API_TOKEN = 'your_jumio_api_token';
    
    // Onfido Configuration
    const ONFIDO_API_URL = 'https://api.onfido.com/v3.6';
    const ONFIDO_API_TOKEN = 'your_onfido_api_token';
    
    // AWS Rekognition Configuration
    const AWS_REGION = 'us-east-1';
    const AWS_ACCESS_KEY_ID = 'your_aws_access_key';
    const AWS_SECRET_ACCESS_KEY = 'your_aws_secret_key';
    
    // Face Recognition Configuration
    const FACE_RECOGNITION_SERVICE = 'aws_rekognition'; // Options: 'aws_rekognition', 'azure_face', 'google_vision'
    
    // Azure Face API Configuration
    const AZURE_FACE_API_KEY = 'your_azure_face_api_key';
    const AZURE_FACE_ENDPOINT = 'https://your-region.api.cognitive.microsoft.com';
    
    // Google Vision API Configuration
    const GOOGLE_VISION_API_KEY = 'your_google_vision_api_key';
    
    // Verification Settings
    const MIN_SIMILARITY_SCORE = 0.7;
    const MIN_LIVENESS_SCORE = 0.8;
    const MAX_VERIFICATION_ATTEMPTS = 3;
    const VERIFICATION_EXPIRY_DAYS = 365;
    
    // File Upload Settings
    const MAX_FILE_SIZE = 5242880; // 5MB in bytes
    const ALLOWED_FILE_TYPES = ['image/jpeg', 'image/png', 'image/jpg'];
    const UPLOAD_PATH = '../../uploads/verification/';
    
    // API Retry Settings
    const MAX_API_RETRIES = 3;
    const API_TIMEOUT_SECONDS = 30;
    
    /**
     * Get configuration for specified service
     * @param string $service
     * @return array
     */
    public static function getServiceConfig($service) {
        switch ($service) {
            case 'jumio':
                return [
                    'api_url' => self::JUMIO_API_URL,
                    'account_id' => self::JUMIO_ACCOUNT_ID,
                    'api_token' => self::JUMIO_API_TOKEN
                ];
            case 'onfido':
                return [
                    'api_url' => self::ONFIDO_API_URL,
                    'api_token' => self::ONFIDO_API_TOKEN
                ];
            case 'aws_rekognition':
                return [
                    'region' => self::AWS_REGION,
                    'access_key' => self::AWS_ACCESS_KEY_ID,
                    'secret_key' => self::AWS_SECRET_ACCESS_KEY
                ];
            case 'azure_face':
                return [
                    'endpoint' => self::AZURE_FACE_ENDPOINT,
                    'api_key' => self::AZURE_FACE_API_KEY
                ];
            case 'google_vision':
                return [
                    'api_key' => self::GOOGLE_VISION_API_KEY
                ];
            default:
                throw new Exception("Unknown service: $service");
        }
    }
    
    /**
     * Check if required configuration is set
     * @param string $service
     * @return bool
     */
    public static function isServiceConfigured($service) {
        try {
            $config = self::getServiceConfig($service);
            foreach ($config as $key => $value) {
                if (empty($value) || strpos($value, 'your_') === 0) {
                    return false;
                }
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
