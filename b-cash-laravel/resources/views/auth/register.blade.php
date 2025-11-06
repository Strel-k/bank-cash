@extends('layouts.guest')

@section('content')
<div class="auth-container">
    <div class="auth-card registration-card">
        <div class="logo">
            <i class="fas fa-wallet"></i> B-Cash
        </div>
        <h1>Create your account</h1>

        @if ($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="step-progress">
            <div class="step active" data-step="1">
                <span class="step-number">1</span>
                <span class="step-label">Basic Info</span>
            </div>
            <div class="step" data-step="2">
                <span class="step-number">2</span>
                <span class="step-label">ID Upload</span>
            </div>
            <div class="step" data-step="3">
                <span class="step-number">3</span>
                <span class="step-label">Face Verify</span>
            </div>
            <div class="step" data-step="4">
                <span class="step-number">4</span>
                <span class="step-label">Security</span>
            </div>
        </div>

        <form method="POST" action="{{ route('register.post') }}" class="auth-form" id="registrationForm" enctype="multipart/form-data">
            @csrf

            <!-- Step 1: Basic Information -->
            <div class="step-content active" data-step="1">
                <div class="form-group">
                    <label for="phone_number">
                        <i class="fas fa-phone"></i> Phone Number
                    </label>
                    <input type="text" 
                           id="phone_number" 
                           name="phone_number" 
                           value="{{ old('phone_number') }}" 
                           pattern="[0-9]{11}" 
                           class="@error('phone_number') is-invalid @enderror"
                           placeholder="09XX XXX XXXX"
                           required>
                    @error('phone_number')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           class="@error('email') is-invalid @enderror"
                           placeholder="Enter your email address"
                           required>
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="full_name">
                        <i class="fas fa-user"></i> Full Name
                    </label>
                    <input type="text" 
                           id="full_name" 
                           name="full_name" 
                           value="{{ old('full_name') }}" 
                           class="@error('full_name') is-invalid @enderror"
                           placeholder="Enter your full name"
                           required>
                    @error('full_name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="birthdate">
                        <i class="fas fa-calendar"></i> Birthdate
                    </label>
                    <input type="date" 
                           id="birthdate" 
                           name="birthdate" 
                           value="{{ old('birthdate') }}" 
                           class="@error('birthdate') is-invalid @enderror"
                           required>
                    @error('birthdate')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="address">
                        <i class="fas fa-map-marker-alt"></i> Address
                    </label>
                    <input type="text" 
                           id="address" 
                           name="address" 
                           value="{{ old('address') }}" 
                           class="@error('address') is-invalid @enderror"
                           placeholder="Enter your complete address"
                           required>
                    @error('address')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="gender">
                        <i class="fas fa-venus-mars"></i> Gender
                    </label>
                    <select id="gender" 
                            name="gender" 
                            class="@error('gender') is-invalid @enderror"
                            required>
                        <option value="">Select Gender</option>
                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('gender')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <button type="button" class="form-button next-step">Continue</button>
            </div>

            <!-- Step 2: ID Verification -->
            <div class="step-content" data-step="2">
                <div class="form-group">
                    <label for="id_type">
                        <i class="fas fa-id-card"></i> ID Type
                    </label>
                    <select id="id_type" 
                            name="id_type" 
                            class="@error('id_type') is-invalid @enderror"
                            required>
                        <option value="">Select ID Type</option>
                        <option value="national_id">National ID</option>
                        <option value="drivers_license">Driver's License</option>
                        <option value="passport">Passport</option>
                    </select>
                    @error('id_type')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-upload"></i> Upload ID (Front)
                    </label>
                    <div class="upload-area" onclick="document.getElementById('id_front').click()">
                        <input type="file" 
                               id="id_front" 
                               name="id_front" 
                               accept="image/*" 
                               class="@error('id_front') is-invalid @enderror"
                               required>
                        <div class="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Click to upload front of ID</span>
                        </div>
                        <img id="id_front_preview" class="upload-preview" style="display: none;">
                    </div>
                    @error('id_front')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-upload"></i> Upload ID (Back)
                    </label>
                    <div class="upload-area" onclick="document.getElementById('id_back').click()">
                        <input type="file" 
                               id="id_back" 
                               name="id_back" 
                               accept="image/*" 
                               class="@error('id_back') is-invalid @enderror"
                               required>
                        <div class="upload-placeholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Click to upload back of ID</span>
                        </div>
                        <img id="id_back_preview" class="upload-preview" style="display: none;">
                    </div>
                    @error('id_back')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="step-buttons">
                    <button type="button" class="form-button prev-step">Back</button>
                    <button type="button" class="form-button next-step">Continue</button>
                </div>
            </div>

            <!-- Step 3: Face Verification -->
            <div class="step-content" data-step="3">
                <div class="face-verify-container">
                    <div id="camera-container">
                        <video id="camera" autoplay playsinline></video>
                        <canvas id="preview" style="display: none;"></canvas>
                    </div>
                    <input type="hidden" name="face_image" id="face_image">
                    <div class="camera-controls">
                        <button type="button" class="form-button" id="startCamera">
                            <i class="fas fa-camera"></i> Start Camera
                        </button>
                        <button type="button" class="form-button" id="capturePhoto" style="display: none;">
                            <i class="fas fa-camera"></i> Take Photo
                        </button>
                        <button type="button" class="form-button" id="retakePhoto" style="display: none;">
                            <i class="fas fa-redo"></i> Retake
                        </button>
                    </div>
                    @error('face_image')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="step-buttons">
                    <button type="button" class="form-button prev-step">Back</button>
                    <button type="button" class="form-button next-step">Continue</button>
                </div>
            </div>

            <!-- Step 4: Security -->
            <div class="step-content" data-step="4">
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="password-input-group">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="@error('password') is-invalid @enderror"
                               placeholder="Create a strong password"
                               required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">
                        <i class="fas fa-lock"></i> Confirm Password
                    </label>
                    <div class="password-input-group">
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               placeholder="Confirm your password"
                               required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password_confirmation')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="pin">
                        <i class="fas fa-key"></i> 6-Digit PIN
                    </label>
                    <input type="password" 
                           id="pin" 
                           name="pin" 
                           pattern="[0-9]{6}" 
                           maxlength="6" 
                           class="@error('pin') is-invalid @enderror"
                           placeholder="Create a 6-digit PIN"
                           required>
                    @error('pin')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="pin_confirmation">
                        <i class="fas fa-key"></i> Confirm PIN
                    </label>
                    <input type="password" 
                           id="pin_confirmation" 
                           name="pin_confirmation" 
                           pattern="[0-9]{6}" 
                           maxlength="6" 
                           placeholder="Confirm your PIN"
                           required>
                </div>

                <div class="form-group">
                    <label class="checkbox-container">
                        <input type="checkbox" name="terms" required>
                        <span class="checkmark"></span>
                        I agree to the <a href="#" class="link">Terms and Conditions</a> and <a href="#" class="link">Privacy Policy</a>
                    </label>
                    @error('terms')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="step-buttons">
                    <button type="button" class="form-button prev-step">Back</button>
                    <button type="submit" class="form-button register-button">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </div>
            </div>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="{{ route('login') }}">Login</a></p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/verification.css') }}">
@endpush

@push('scripts')
<!-- Face-API.js for facial recognition -->
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Step navigation
    const steps = document.querySelectorAll('.step');
    const stepContents = document.querySelectorAll('.step-content');
    const nextButtons = document.querySelectorAll('.next-step');
    const prevButtons = document.querySelectorAll('.prev-step');
    let currentStep = 1;

    function updateStep(step) {
        steps.forEach(s => s.classList.remove('active'));
        stepContents.forEach(c => c.classList.remove('active'));
        
        steps[step - 1].classList.add('active');
        stepContents[step - 1].classList.add('active');
        currentStep = step;
    }

    nextButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (validateStep(currentStep)) {
                updateStep(currentStep + 1);
            }
        });
    });

    prevButtons.forEach(button => {
        button.addEventListener('click', () => {
            updateStep(currentStep - 1);
        });
    });

    // Form validation per step
    function validateStep(step) {
        const currentStepContent = document.querySelector(`.step-content[data-step="${step}"]`);
        const inputs = currentStepContent.querySelectorAll('input[required], select[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!input.value) {
                isValid = false;
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        });

        return isValid;
    }

    // Phone number formatting
    const phoneInput = document.getElementById('phone_number');
    phoneInput.addEventListener('input', function(e) {
        let value = this.value.replace(/\D/g, '').substring(0, 11);
        if (value.length >= 11) {
            this.value = value;
        }
    });

    // PIN input restrictions
    const pinInputs = document.querySelectorAll('input[pattern="[0-9]{6}"]');
    pinInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
        });
    });

    // ID image preview
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        const placeholder = input.parentElement.querySelector('.upload-placeholder');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.getElementById('id_front').addEventListener('change', function() {
        previewImage(this, 'id_front_preview');
    });

    document.getElementById('id_back').addEventListener('change', function() {
        previewImage(this, 'id_back_preview');
    });

    // Face verification
    let stream = null;
    const startCameraBtn = document.getElementById('startCamera');
    const capturePhotoBtn = document.getElementById('capturePhoto');
    const retakePhotoBtn = document.getElementById('retakePhoto');
    const video = document.getElementById('camera');
    const canvas = document.getElementById('preview');
    const faceImage = document.getElementById('face_image');

    startCameraBtn.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'user' } 
            });
            video.srcObject = stream;
            startCameraBtn.style.display = 'none';
            capturePhotoBtn.style.display = 'inline-block';
            await loadFaceDetectionModel();
        } catch (err) {
            console.error('Error accessing camera:', err);
            alert('Could not access camera. Please ensure you have granted camera permissions.');
        }
    });

    async function loadFaceDetectionModel() {
        try {
            await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
            await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
            
            // Start face detection
            setInterval(async () => {
                const detections = await faceapi.detectSingleFace(
                    video, 
                    new faceapi.TinyFaceDetectorOptions()
                ).withFaceLandmarks();
                
                if (detections) {
                    document.getElementById('face-guide').style.borderColor = '#4CAF50';
                    capturePhotoBtn.disabled = false;
                } else {
                    document.getElementById('face-guide').style.borderColor = '#ff0000';
                    capturePhotoBtn.disabled = true;
                }
            }, 100);
        } catch (err) {
            console.error('Error loading face detection model:', err);
        }
    }

    capturePhotoBtn.addEventListener('click', () => {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        
        // Convert canvas to base64
        faceImage.value = canvas.toDataURL('image/jpeg');
        
        // Show preview and retake button
        video.style.display = 'none';
        canvas.style.display = 'block';
        capturePhotoBtn.style.display = 'none';
        retakePhotoBtn.style.display = 'inline-block';
        
        // Stop the camera stream
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    });

    retakePhotoBtn.addEventListener('click', async () => {
        // Reset everything
        video.style.display = 'block';
        canvas.style.display = 'none';
        retakePhotoBtn.style.display = 'none';
        
        // Restart camera
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'user' } 
            });
            video.srcObject = stream;
            capturePhotoBtn.style.display = 'inline-block';
        } catch (err) {
            console.error('Error accessing camera:', err);
            alert('Could not access camera. Please ensure you have granted camera permissions.');
        }
    });
});

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = event.currentTarget.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endpush