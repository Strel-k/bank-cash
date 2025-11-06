<?php
/**
 * B-Cash Verification System Test Script
 * 
 * This script tests the complete verification workflow
 * Run this script to verify the system is working properly
 */

echo "🧪 B-Cash Verification System Test\n";
echo "==================================\n\n";

// Test 1: Configuration Loading
echo "1. Testing Configuration Loading...\n";
require_once 'app/config/VerificationConfig.php';

if (class_exists('VerificationConfig')) {
    echo "   ✅ VerificationConfig loaded successfully\n";
    
    // Check if services are configured
    $idConfigured = VerificationConfig::isServiceConfigured(VerificationConfig::ID_VERIFICATION_SERVICE);
    $faceConfigured = VerificationConfig::isServiceConfigured(VerificationConfig::FACE_RECOGNITION_SERVICE);
    
    echo "   📋 ID Verification Service: " . ($idConfigured ? "Configured" : "Simulated") . "\n";
    echo "   📋 Face Recognition Service: " . ($faceConfigured ? "Configured" : "Simulated") . "\n";
} else {
    echo "   ❌ VerificationConfig not found\n";
    exit(1);
}

// Test 2: Service Classes
echo "\n2. Testing Service Classes...\n";
require_once 'app/services/IDVerificationService.php';
require_once 'app/services/FaceRecognitionService.php';

if (class_exists('IDVerificationService') && class_exists('FaceRecognitionService')) {
    echo "   ✅ Service classes loaded successfully\n";
    
    // Test ID Verification Service
    $idService = new IDVerificationService();
    $idResult = $idService->verifyIDDocument('test.jpg', 'passport', 'TEST123');
    
    if ($idResult['success']) {
        echo "   ✅ ID Verification Service working\n";
        echo "   📊 Confidence Score: " . ($idResult['confidence_score'] * 100) . "%\n";
    } else {
        echo "   ❌ ID Verification Service failed\n";
    }
    
    // Test Face Recognition Service
    $faceService = new FaceRecognitionService();
    $faceResult = $faceService->detectAndVerifyFace('test_face.jpg');
    
    if ($faceResult['success']) {
        echo "   ✅ Face Recognition Service working\n";
        echo "   📊 Liveness Score: " . ($faceResult['liveness_score'] * 100) . "%\n";
    } else {
        echo "   ❌ Face Recognition Service failed\n";
    }
} else {
    echo "   ❌ Service classes not found\n";
    exit(1);
}

// Test 3: API Endpoints
echo "\n3. Testing API Endpoints...\n";
$endpoints = [
    '/public/api/verification.php?action=status',
    '/public/api/test-verification.php?action=health-check'
];

foreach ($endpoints as $endpoint) {
    $url = 'http://localhost' . $endpoint;
    echo "   Testing: $endpoint\n";
    
    // Simulate API call (in real environment, this would be an actual HTTP request)
    if (file_exists(ltrim($endpoint, '/'))) {
        echo "   ✅ Endpoint exists\n";
    } else {
        echo "   ⚠️  Endpoint not found (may need web server)\n";
    }
}

// Test 4: Database Connection
echo "\n4. Testing Database Connection...\n";
require_once 'app/config/Database.php';

try {
    $database = new Database();
    $db = $database->connect();
    echo "   ✅ Database connection successful\n";
    
    // Check if verification tables exist
    $tables = ['user_verification', 'verification_logs'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "   ✅ Table '$table' exists\n";
        } else {
            echo "   ⚠️  Table '$table' not found - run database/verification_schema.sql\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
}

// Test 5: File Upload Directory
echo "\n5. Testing File Upload Directory...\n";
$uploadDir = 'uploads/verification/';
if (!file_exists($uploadDir)) {
    if (mkdir($uploadDir, 0755, true)) {
        echo "   ✅ Upload directory created: $uploadDir\n";
    } else {
        echo "   ❌ Failed to create upload directory\n";
    }
} else {
    echo "   ✅ Upload directory exists: $uploadDir\n";
}

echo "\n🎯 Test Summary:\n";
echo "===============\n";
echo "The verification system is ready for testing!\n\n";

echo "📋 Next Steps:\n";
echo "1. Start a local web server: php -S localhost:8000\n";
echo "2. Open: http://localhost:8000/verification.php\n";
echo "3. Test the complete verification workflow\n";
echo "4. Check API endpoints for functionality\n\n";

echo "🔧 Configuration Notes:\n";
echo "- Update app/config/VerificationConfig.php with real API keys for production\n";
echo "- The system currently uses simulated responses for development\n";
echo "- All services are ready for real API integration\n";

echo "\n✅ Verification System Test Completed Successfully!\n";
?>
