<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID & Face Verification - B-Cash</title>
    <link rel="stylesheet" href="css/verification.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="verification-container">
        <div class="header">
            <h1><i class="fas fa-shield-alt"></i> Identity Verification</h1>
            <p>Complete your verification to unlock full account features</p>
        </div>

        <div class="verification-steps">
            <div class="step active" id="step1">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h3>Upload ID Document</h3>
                    <p>Take clear photos of your government-issued ID</p>
                </div>
            </div>
            <div class="step" id="step2">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h3>Take Selfie</h3>
                    <p>Capture your face for verification</p>
                </div>
            </div>
            <div class="step" id="step3">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h3>Verification Complete</h3>
                    <p>Review and submit for processing</p>
                </div>
            </div>
        </div>

        <div class="verification-form">
            <!-- Step 1: ID Document Upload -->
            <div class="form-section active" id="section1">
                <h3><i class="fas fa-id-card"></i> ID Document Upload</h3>
                
                <div class="document-type-selector">
                    <label>Select Document Type:</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="documentType" value="passport" checked>
                            <span><i class="fas fa-passport"></i> Passport</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="documentType" value="drivers_license">
                            <span><i class="fas fa-id-card"></i> Driver's License</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="documentType" value="national_id">
                            <span><i class="fas fa-id-badge"></i> National ID</span>
                        </label>
                    </div>
                </div>

                <div class="document-number-input">
                    <label for="documentNumber">Document Number:</label>
                    <input type="text" id="documentNumber" placeholder="Enter your document number">
                </div>

                <div class="upload-section">
                    <div class="upload-box" id="frontUpload">
                        <i class="fas fa-camera"></i>
                        <h4>Front Side</h4>
                        <p>Take a clear photo of the front</p>
                        <input type="file" id="frontDocument" accept="image/*" capture="environment">
                        <img id="frontPreview" style="display: none;">
                    </div>
                    
                    <div class="upload-box" id="backUpload">
                        <i class="fas fa-camera"></i>
                        <h4>Back Side</h4>
                        <p>Take a clear photo of the back</p>
                        <input type="file" id="backDocument" accept="image/*" capture="environment">
                        <img id="backPreview" style="display: none;">
                    </div>
                </div>

                <button type="button" class="btn-next" onclick="nextStep(2)">
                    Continue to Selfie <i class="fas fa-arrow-right"></i>
                </button>
            </div>

            <!-- Step 2: Face Verification -->
            <div class="form-section" id="section2">
                <h3><i class="fas fa-user-circle"></i> Face Verification</h3>
                
                <div class="face-verification-container">
                    <div class="camera-container">
                        <video id="video" autoplay muted></video>
                        <canvas id="canvas" style="display: none;"></canvas>
                        <div class="camera-overlay">
                            <div class="face-guide"></div>
                            <div class="instructions">
                                <p>Position your face within the frame</p>
                                <p>Look directly at the camera</p>
                                <p>Ensure good lighting</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="camera-controls">
                        <button type="button" class="btn-capture" onclick="captureSelfie()">
                            <i class="fas fa-camera"></i> Capture Selfie
                        </button>
                        <button type="button" class="btn-retake" onclick="retakeSelfie()" style="display: none;">
                            <i class="fas fa-redo"></i> Retake
                        </button>
                    </div>
                    
                    <img id="selfiePreview" style="display: none;">
                </div>

                <div class="liveness-check">
                    <h4>Liveness Check</h4>
                    <p>Please blink your eyes and turn your head slightly to prove you're a real person</p>
                    <div class="liveness-indicator">
                        <div class="indicator-dot active"></div>
                        <div class="indicator-dot"></div>
                        <div class="indicator-dot"></div>
                    </div>
                </div>

                <div class="step-buttons">
                    <button type="button" class="btn-prev" onclick="prevStep(1)">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <button type="button" class="btn-next" onclick="nextStep(3)">
                        Review & Submit <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- Step 3: Review and Submit -->
            <div class="form-section" id="section3">
                <h3><i class="fas fa-check-circle"></i> Review Your Submission</h3>
                
                <div class="review-section">
                    <div class="review-item">
                        <h4>Document Type</h4>
                        <p id="reviewDocumentType">-</p>
                    </div>
                    <div class="review-item">
                        <h4>Document Number</h4>
                        <p id="reviewDocumentNumber">-</p>
                    </div>
                    <div class="review-item">
                        <h4>ID Photos</h4>
                        <div class="review-images">
                            <img id="reviewFront" style="display: none;">
                            <img id="reviewBack" style="display: none;">
                        </div>
                    </div>
                    <div class="review-item">
                        <h4>Selfie Photo</h4>
                        <img id="reviewSelfie" style="display: none;">
                    </div>
                </div>

                <div class="terms-section">
                    <label class="checkbox-label">
                        <input type="checkbox" id="termsAgreement">
                        I agree to the <a href="#" onclick="showTerms()">Terms and Conditions</a> and <a href="#" onclick="showPrivacy()">Privacy Policy</a>
                    </label>
                </div>

                <div class="step-buttons">
                    <button type="button" class="btn-prev" onclick="prevStep(2)">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <button type="button" class="btn-submit" onclick="submitVerification()" disabled>
                        <i class="fas fa-paper-plane"></i> Submit for Verification
                    </button>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="progress-container">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <p class="progress-text">Step <span id="currentStep">1</span> of 3</p>
        </div>

        <!-- Loading Overlay -->
        <div class="loading-overlay" id="loadingOverlay" style="display: none;">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Processing your verification...</p>
            </div>
        </div>

        <!-- Results Modal -->
        <div class="modal" id="resultsModal" style="display: none;">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <div id="modalContent"></div>
            </div>
        </div>
    </div>

    <script src="js/verification.js"></script>
</body>
</html>
