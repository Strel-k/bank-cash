@extends('layouts.app')

@section('title', 'Verification - B-Cash')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/verification.css') }}">
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Account Verification</h1>
            <p>Please complete your account verification to access all features.</p>
        </div>

        <div id="verificationStatus" class="alert" style="display: none;"></div>

        <div id="verificationStep1" class="verification-step">
            <h3>Step 1: Document Information</h3>
            <form id="documentInfoForm">
                <div class="form-group">
                    <label for="document_type">Document Type:</label>
                    <select id="document_type" name="document_type" required>
                        <option value="">Select Document Type</option>
                        <option value="passport">Passport</option>
                        <option value="national_id">National ID</option>
                        <option value="drivers_license">Driver's License</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="document_number">Document Number:</label>
                    <input type="text" id="document_number" name="document_number" required>
                </div>
                <button type="submit" class="form-button">Next</button>
            </form>
        </div>

        <div id="verificationStep2" class="verification-step" style="display: none;">
            <h3>Step 2: Upload ID (Front Only)</h3>
            <form id="documentUploadForm">
                <input type="hidden" id="verification_id">
                <div class="form-group">
                    <label>Front of Document:</label>
                    <div class="upload-area" id="frontUploadArea">
                        <div class="upload-placeholder">
                            <i class="fas fa-upload"></i>
                            <p>Click or drop image here</p>
                        </div>
                        <img id="frontPreview" style="display: none; max-width: 100%; height: auto;">
                        <input type="file" id="document_front" name="document_front" accept="image/*" required>
                    </div>
                </div>
                <button type="submit" class="form-button">Next</button>
            </form>
        </div>

        <div id="verificationStep3" class="verification-step" style="display: none;">
            <h3>Step 3: Face Verification</h3>
            <div class="webcam-section">
                <div id="webcam-container">
                    <video id="webcam" autoplay playsinline></video>
                    <div id="face-oval"></div>
                    <canvas id="canvas" style="display: none;"></canvas>
                    <img id="capturedImage" style="display: none; max-width: 100%; height: auto;">
                </div>
                <div class="webcam-controls">
                    <button id="startCamera" class="form-button"><i class="fas fa-camera"></i> Start Camera</button>
                    <button id="captureImage" class="form-button" style="display: none;"><i class="fas fa-camera"></i> Capture</button>
                    <button id="retakeImage" class="form-button" style="display: none;"><i class="fas fa-redo"></i> Retake</button>
                    <button id="submitImage" class="form-button" style="display: none;"><i class="fas fa-check"></i> Submit</button>
                </div>
            </div>
        </div>

        <div id="verificationComplete" class="verification-step text-center" style="display: none;">
            <i class="fas fa-clock-o" style="font-size: 48px; color: var(--gcash-blue);"></i>
            <h3>Verification Submitted</h3>
            <p>Your documents have been submitted for review. Please allow 24-48 hours for verification.</p>
            <p>You will be notified once your account is verified.</p>
            <a href="{{ route('dashboard') }}" class="form-button">Return to Dashboard</a>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let stream = null;
let verificationId = null;

function showError(message) {
    const statusDiv = document.getElementById('verificationStatus');
    statusDiv.className = 'alert alert-error';
    statusDiv.textContent = message;
    statusDiv.style.display = 'block';
}

function showSuccess(message) {
    const statusDiv = document.getElementById('verificationStatus');
    statusDiv.className = 'alert alert-success';
    statusDiv.textContent = message;
    statusDiv.style.display = 'block';
}

function showStep(stepNumber) {
    document.querySelectorAll('.verification-step').forEach(step => {
        step.style.display = 'none';
    });
    document.getElementById(`verificationStep${stepNumber}`).style.display = 'block';
}

// Step 1: Document Information
document.getElementById('documentInfoForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    try {
        const response = await fetch('/api/verification/start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify({
                document_type: document.getElementById('document_type').value,
                document_number: document.getElementById('document_number').value
            })
        });

        const result = await response.json();
        
        if (response.ok) {
            verificationId = result.verification_id;
            document.getElementById('verification_id').value = verificationId;
            showSuccess('Document information saved');
            showStep(2);
        } else {
            showError(result.message || 'Failed to save document information');
        }
    } catch (error) {
        showError('Failed to start verification process');
    }
});

// Step 2: Document Images
function setupFileUpload(inputId, previewId, uploadAreaId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    const uploadArea = document.getElementById(uploadAreaId);

    uploadArea.addEventListener('click', () => input.click());
    uploadArea.addEventListener('dragover', e => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });
    uploadArea.addEventListener('drop', e => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            input.files = e.dataTransfer.files;
            handleFileSelect(input, preview);
        }
    });

    input.addEventListener('change', () => handleFileSelect(input, preview));
}

function handleFileSelect(input, preview) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
            preview.previousElementSibling.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

setupFileUpload('document_front', 'frontPreview', 'frontUploadArea');
setupFileUpload('document_back', 'backPreview', 'backUploadArea');

document.getElementById('documentUploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    try {
        // Upload front only
        const frontFormData = new FormData();
        frontFormData.append('verification_id', verificationId);
        frontFormData.append('type', 'front');
        frontFormData.append('image', document.getElementById('document_front').files[0]);

        const frontResponse = await fetch('/api/verification/upload-document', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: frontFormData
        });

        if (!frontResponse.ok) {
            throw new Error('Failed to upload front document');
        }

        showSuccess('Document uploaded successfully');
        showStep(3);
    } catch (error) {
        showError(error.message || 'Failed to upload document');
    }
});

// Step 3: Face Verification
document.getElementById('startCamera').addEventListener('click', async function() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: true });
        const video = document.getElementById('webcam');
        video.srcObject = stream;
        this.style.display = 'none';
        document.getElementById('captureImage').style.display = 'inline-block';
    } catch (error) {
        showError('Failed to access camera');
    }
});

document.getElementById('captureImage').addEventListener('click', function() {
    const video = document.getElementById('webcam');
    const canvas = document.getElementById('canvas');
    const capturedImage = document.getElementById('capturedImage');
    
    // Set canvas dimensions to match video
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Draw video frame to canvas
    canvas.getContext('2d').drawImage(video, 0, 0);
    
    // Convert canvas to image
    capturedImage.src = canvas.toDataURL('image/jpeg');
    capturedImage.style.display = 'block';
    video.style.display = 'none';
    
    // Show/hide appropriate buttons
    this.style.display = 'none';
    document.getElementById('retakeImage').style.display = 'inline-block';
    document.getElementById('submitImage').style.display = 'inline-block';
});

document.getElementById('retakeImage').addEventListener('click', function() {
    const video = document.getElementById('webcam');
    const capturedImage = document.getElementById('capturedImage');
    
    // Show video, hide captured image
    video.style.display = 'block';
    capturedImage.style.display = 'none';
    
    // Show/hide appropriate buttons
    this.style.display = 'none';
    document.getElementById('submitImage').style.display = 'none';
    document.getElementById('captureImage').style.display = 'inline-block';
});

document.getElementById('submitImage').addEventListener('click', async function() {
    try {
        // Convert canvas content to blob
        const canvas = document.getElementById('canvas');
        const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg'));
        
        // Create form data and append image
        const formData = new FormData();
        formData.append('verification_id', verificationId);
        formData.append('image', blob, 'face.jpg');
        
        const response = await fetch('/api/verification/upload-face', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: formData
        });

        if (response.ok) {
            showSuccess('Face image uploaded successfully');
            
            // Stop the camera stream
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            
            // Show completion message
            document.querySelectorAll('.verification-step').forEach(step => {
                step.style.display = 'none';
            });
            document.getElementById('verificationComplete').style.display = 'block';
        } else {
            const result = await response.json();
            showError(result.message || 'Failed to upload face image');
        }
    } catch (error) {
        showError('Failed to upload face image');
    }
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }
});
</script>
@endpush