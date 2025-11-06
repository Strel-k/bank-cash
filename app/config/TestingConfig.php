<?php
class TestingConfig {
    // Enable testing mode
    const TESTING_MODE = true;
    
    // Test data for ID verification
    const TEST_DOCUMENTS = [
        'passport' => [
            'number' => 'P123456789',
            'valid' => true,
            'confidence' => 0.95
        ],
        'drivers_license' => [
            'number' => 'DL123456789',
            'valid' => true,
            'confidence' => 0.92
        ],
        'national_id' => [
            'number' => 'ID123456789',
            'valid' => true,
            'confidence' => 0.90
        ]
    ];
    
    // Test face data
    const TEST_FACE_DATA = [
        'liveness_score' => 0.88,
        'similarity_score' => 0.85,
        'face_detected' => true,
        'is_live' => true
    ];
    
    // Test file paths (use existing test images)
    const TEST_IMAGE_PATHS = [
        'passport' => 'tests/images/test_passport.jpg',
        'drivers_license' => 'tests/images/test_license.jpg',
        'face' => 'tests/images/test_face.jpg'
    ];
}
?>
