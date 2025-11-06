<?php
// Start output buffering to prevent any unwanted output
ob_start();

// Set error reporting to prevent warnings from breaking JSON
error_reporting(0);
ini_set('display_errors', 0);

require_once '../../app/controllers/VerificationController.php';
require_once '../../app/services/IDVerificationService.php';
require_once '../../app/services/FaceRecognitionService.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'test-id-verification':
        testIDVerification();
        break;
    case 'test-face-verification':
        testFaceVerification();
        break;
    case 'test-complete-flow':
        testCompleteFlow();
        break;
    case 'health-check':
        healthCheck();
        break;
    default:
        echo json_encode(['error' => 'Invalid test action']);
}

function testIDVerification() {
    $idService = new IDVerificationService();
    
    // Test with different document types
    $testCases = [
        ['passport', 'P123456789'],
        ['drivers_license', 'DL987654321'],
        ['national_id', 'ID555555555']
    ];
    
    $results = [];
    foreach ($testCases as [$type, $number]) {
        $result = $idService->verifyIDDocument('test_image.jpg', $type, $number);
        $results[] = [
            'document_type' => $type,
            'document_number' => $number,
            'result' => $result
        ];
    }
    
    echo json_encode(['success' => true, 'results' => $results]);
}

function testFaceVerification() {
    $faceService = new FaceRecognitionService();
    
    $result = $faceService->detectAndVerifyFace('test_face.jpg');
    $liveness = $faceService->performLivenessCheck('test_face.jpg');
    
    echo json_encode([
        'success' => true,
        'face_detection' => $result,
        'liveness_check' => $liveness
    ]);
}

function testCompleteFlow() {
    // Simulate complete verification flow
    echo json_encode([
        'success' => true,
        'flow' => [
            'step1' => 'Document upload',
            'step2' => 'Face capture',
            'step3' => 'Verification',
            'step4' => 'Result'
        ],
        'estimated_time' => '2-3 minutes'
    ]);
}

function healthCheck() {
    $idService = new IDVerificationService();
    $faceService = new FaceRecognitionService();
    
    echo json_encode([
        'success' => true,
        'services' => [
            'id_verification' => $idService->isConfigured() ? 'configured' : 'simulated',
            'face_recognition' => $faceService->isConfigured() ? 'configured' : 'simulated'
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
