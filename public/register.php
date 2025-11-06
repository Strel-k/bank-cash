<?php
session_start();
require_once __DIR__ . '/../app/config/Config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - B-Cash</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Enhanced styles for integrated verification */
        .registration-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .progress-section {
            margin-bottom: 2rem;
            background: var(--card-bg);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }
        
        .progress-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            margin-bottom: 1rem;
        }
        
        .progress-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 10%;
            right: 10%;
            height: 2px;
            background: var(--border-color);
            z-index: 1;
        }
        
        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            flex: 1;
        }
        
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--border-color);
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .progress-step.active .step-circle {
            background: var(--gcash-green);
            color: white;
        }
        
        .progress-step.completed .step-circle {
            background: var(--gcash-green);
            color: white;
        }
        
        .step-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            text-align: center;
            max-width: 80px;
        }
        
        .progress-step.active .step-label {
            color: var(--gcash-green);
            font-weight: 600;
        }
        
        .progress-bar-container {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            margin: 1rem 0;
        }
        
        .progress-fill {
            height: 100%;
            background: var(--gcash-green);
            transition: width 0.3s ease;
        }
        
        /* Sliding form sections */
        .form-sections-container {
            position: relative;
            overflow: hidden;
            min-height: 400px;
        }
        
        .form-section {
            position: absolute;
            width: 100%;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.4s ease-in-out;
            pointer-events: none;
        }
        
        .form-section.active {
            opacity: 1;
            transform: translateX(0);
            pointer-events: all;
            position: relative;
        }
        
        .form-section.slide-left {
            transform: translateX(-100%);
        }
        
        .form-section.slide-right {
            transform: translateX(100%);
        }
        
        .upload-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin: 1rem 0;
        }
        
        .upload-area {
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            padding: 2rem 1rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: var(--card-bg);
        }

        .upload-area:hover {
            border-color: var(--gcash-green);
            background: rgba(0, 177, 79, 0.1);
        }

        .upload-area.dragover {
            border-color: var(--gcash-green);
            background: rgba(0, 177, 79, 0.2);
        }
        
        .upload-area.active {
            border-color: var(--gcash-green);
            background: #e8f5e8;
        }
        
        .upload-icon {
            font-size: 2rem;
            color: #999;
            margin-bottom: 0.5rem;
        }
        
        .upload-text {
            font-size: 0.875rem;
            color: #666;
            margin-bottom: 0.5rem;
        }
        
        .upload-input {
            display: none;
        }
        
        .upload-preview {
            max-width: 100%;
            max-height: 150px;
            border-radius: 4px;
            margin-top: 1rem;
            display: none;
        }
        
        .upload-preview.visible {
            display: block;
        }
        
        .camera-container {
            text-align: center;
            padding: 2rem;
            background: var(--card-bg);
            border-radius: 8px;
            margin: 1rem 0;
            border: 1px solid var(--border-color);
        }
        
        .camera-preview {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: #ddd;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #999;
        }
        
        .camera-preview.has-image {
            background: transparent;
            border: 2px solid var(--gcash-green);
        }
        
        .step-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        
        .btn-step {
            padding: 0.625rem 1.25rem;
            border: none;
            border-radius: 6px;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-prev {
            background: var(--card-bg);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-next {
            background: var(--gcash-green);
            color: white;
        }

        .btn-prev:hover {
            background: var(--border-color);
        }
        
        .btn-next:hover {
            background: #00a855;
        }
        
        .btn-prev:disabled,
        .btn-next:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .form-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        
        .card-header {
            padding: 2rem;
            text-align: center;
            background: linear-gradient(135deg, var(--gcash-green), var(--gcash-blue));
            color: white;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--card-bg);
            color: var(--text-primary);
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--gcash-green);
            box-shadow: 0 0 0 3px rgba(0, 177, 79, 0.1);
        }

        .form-input::placeholder {
            color: var(--text-secondary);
        }
        
        .form-button {
            width: 80%;
            padding: 0.75rem 1.25rem;
            background: var(--gcash-green);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin: 1rem auto 0;
        }
        
        .form-button:hover {
            background: #00a855;
        }
        
        .form-button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .success-message {
            color: var(--gcash-green);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .verification-status {
            padding: 1rem;
            border-radius: 6px;
            margin: 1rem 0;
            text-align: center;
        }
        
        .verification-status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .verification-status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .verification-status.pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        @media (max-width: 768px) {
            .upload-grid {
                grid-template-columns: 1fr;
            }
            
            .progress-steps {
                flex-direction: column;
                gap: 1rem;
            }
            
            .progress-steps::before {
                display: none;
            }
            
            .registration-container {
                padding: 1rem;
            }
            
            .card-header,
            .card-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-wallet"></i> B-Cash
            </div>
            <nav class="nav-links">
                <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
        </div>
    </header>

    <main>
        <div class="registration-container">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-plus" style="font-size: 48px; margin-bottom: 1rem;"></i>
                    <h1 class="card-title">Create Account</h1>
                    <p>Join millions of users on B-Cash</p>
                </div>
                
                <div class="card-body">
                    <!-- Progress Section -->
                    <div class="progress-section">
                        <div class="progress-steps">
                            <div class="progress-step active" data-step="1">
                                <div class="step-circle">1</div>
                                <div class="step-label">Basic Info</div>
                            </div>
                            <div class="progress-step" data-step="2">
                                <div class="step-circle">2</div>
                                <div class="step-label">ID Upload</div>
                            </div>
                            <div class="progress-step" data-step="3">
                                <div class="step-circle">3</div>
                                <div class="step-label">Face Verify</div>
                            </div>
                            <div class="progress-step" data-step="4">
                                <div class="step-circle">4</div>
                                <div class="step-label">Complete</div>
                            </div>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-fill" id="progressFill" style="width: 25%"></div>
                        </div>
                    </div>
                    
                    <!-- Form Sections Container -->
                    <div class="form-sections-container">
                        <!-- Step 1: Basic Information -->
                        <form id="step1Form" class="form-section active">
                            <h3 style="text-align: center; margin-bottom: 2rem; color: var(--gcash-green);">
                                <i class="fas fa-user-circle"></i> Personal Information
                            </h3>
                            
                            <div class="form-group">
                                <label class="form-label" for="full_name">
                                    <i class="fas fa-user"></i> Full Name
                                </label>
                                <input type="text" 
                                       class="form-input" 
                                       name="full_name" 
                                       placeholder="Enter your full name" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="phone_number">
                                    <i class="fas fa-phone"></i> Phone Number
                                </label>
                                <input type="tel" 
                                       class="form-input" 
                                       name="phone_number" 
                                       placeholder="09XX XXX XXXX" 
                                       required 
                                       pattern="[0-9]{11}">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="email">
                                    <i class="fas fa-envelope"></i> Email Address
                                </label>
                                <input type="email" 
                                       class="form-input" 
                                       name="email" 
                                       placeholder="Enter your email" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="password">
                                    <i class="fas fa-lock"></i> Password
                                </label>
                                <input type="password" 
                                       class="form-input" 
                                       name="password" 
                                       placeholder="Create a strong password" 
                                       required 
                                       minlength="6">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="confirm_password">
                                    <i class="fas fa-lock"></i> Confirm Password
                                </label>
                                <input type="password" 
                                       class="form-input" 
                                       name="confirm_password" 
                                       placeholder="Confirm your password" 
                                       required 
                                       minlength="6">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="birthdate">
                                    <i class="fas fa-calendar"></i> Birthdate
                                </label>
                                <input type="date" 
                                       class="form-input" 
                                       name="birthdate" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="address">
                                    <i class="fas fa-map-marker-alt"></i> Address
                                </label>
                                <input type="text" 
                                       class="form-input" 
                                       name="address" 
                                       placeholder="Enter your complete address" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-venus-mars"></i> Gender
                                </label>
                                <select class="form-input" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div class="step-buttons">
                                <button type="button" class="btn-next" onclick="nextStep()">
                                    Next: ID Verification <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </form>

                        <!-- Step 2: ID Verification -->
                        <div id="step2Form" class="form-section">
                            <h3 style="text-align: center; margin-bottom: 2rem; color: var(--gcash-green);">
                                <i class="fas fa-id-card"></i> ID Verification
                            </h3>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-id-card"></i> ID Document Type
                                </label>
                                <select class="form-input" name="document_type" required>
                                    <option value="">Select Document Type</option>
                                    <option value="national_id">National ID</option>
                                    <option value="passport">Passport</option>
                                    <option value="drivers_license">Driver's License</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="document_number">
                                    <i class="fas fa-hashtag"></i> Document Number
                                </label>
                                <input type="text" 
                                       class="form-input" 
                                       name="document_number" 
                                       placeholder="Enter your document number" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-camera"></i> Upload ID Document
                                </label>
                                <div class="upload-grid">
                                    <div class="upload-area" id="frontIdUpload" onclick="document.getElementById('frontIdInput').click()">
                                        <i class="fas fa-id-card upload-icon"></i>
                                        <div class="upload-text">Front Side</div>
                                        <input type="file" id="frontIdInput" class="upload-input" accept="image/*" onchange="previewImage(this, 'frontIdPreview')">
                                        <img id="frontIdPreview" class="upload-preview" alt="Front ID Preview">
                                    </div>
                                    <div class="upload-area" id="backIdUpload" onclick="document.getElementById('backIdInput').click()">
                                        <i class="fas fa-id-card upload-icon"></i>
                                        <div class="upload-text">Back Side</div>
                                        <input type="file" id="backIdInput" class="upload-input" accept="image/*" onchange="previewImage(this, 'backIdPreview')">
                                        <img id="backIdPreview" class="upload-preview" alt="Back ID Preview">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="step-buttons">
                                <button type="button" class="btn-prev" onclick="prevStep()">
                                    <i class="fas fa-arrow-left"></i> Previous
                                </button>
                                <button type="button" class="btn-next" onclick="nextStep()">
                                    Next: Face Verification <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Face Verification -->
                        <div id="step3Form" class="form-section">
                            <h3 style="text-align: center; margin-bottom: 2rem; color: var(--gcash-green);">
                                <i class="fas fa-user-check"></i> Face Verification
                            </h3>
                            
                            <div class="camera-container">
                                <div class="camera-preview" id="facePreview">
                                    <i class="fas fa-user"></i>
                                </div>
                                <button type="button" class="btn-step" onclick="captureFace()" style="margin: 0 auto;">
                                    <i class="fas fa-camera"></i> Start Camera
                                </button>
                                <div style="text-align: center; margin-top: 1rem;">
                                    <p style="color: #666; font-size: 0.875rem; margin-bottom: 0.5rem;">
                                        <i class="fas fa-shield-alt"></i> <strong>Security Checkpoint</strong>
                                    </p>
                                    <p style="color: #856404; font-size: 0.75rem; background: #fff3cd; padding: 8px; border-radius: 4px; margin: 0;">
                                        <i class="fas fa-exclamation-triangle"></i> Face verification must pass before account creation
                                    </p>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-checkbox" style="display: flex; align-items: center; gap: 0.5rem;">
                                    <input type="checkbox" name="terms" required>
                                    <span>I agree to the <a href="#" style="color: var(--gcash-green);">Terms and Conditions</a> and <a href="#" style="color: var(--gcash-green);">Privacy Policy</a></span>
                                </label>
                            </div>
                            
                            <div class="step-buttons">
                                <button type="button" class="btn-prev" onclick="prevStep()">
                                    <i class="fas fa-arrow-left"></i> Previous
                                </button>
                                <button type="button" class="btn-next" id="completeRegistration" onclick="completeRegistration()">
                                    <i class="fas fa-check"></i> Complete Registration
                                </button>
                            </div>
                        </div>

                        <!-- Step 4: Completion -->
                        <div id="step4Form" class="form-section">
                            <div style="text-align: center; padding: 2rem;">
                                <i class="fas fa-check-circle" style="font-size: 4rem; color: var(--gcash-green); margin-bottom: 1rem;"></i>
                                <h2>Registration Complete!</h2>
                                <p>Your account has been created successfully. Please check your email for verification.</p>
                                <a href="login.php" class="form-button" style="display: inline-block; margin-top: 1rem;">
                                    <i class="fas fa-sign-in-alt"></i> Go to Login
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Success Message -->
                    <div id="successMessage" style="display: none; text-align: center; padding: 2rem;">
                        <i class="fas fa-check-circle" style="font-size: 4rem; color: var(--gcash-green); margin-bottom: 1rem;"></i>
                        <h2>Registration Successful!</h2>
                        <p>Your account has been created successfully. Please check your email for verification.</p>
                        <a href="login.php" class="form-button" style="display: inline-block; margin-top: 1rem;">
                            <i class="fas fa-sign-in-alt"></i> Go to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

   

    <script src="js/auth.js"></script>
    <!-- Face-API.js for real AI facial recognition -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="js/face-recognition.js"></script>
    <script src="js/register-verification-fixed.js"></script>
</body>
</html>
