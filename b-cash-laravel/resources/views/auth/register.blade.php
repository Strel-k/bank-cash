<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B-Cash | Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(180deg, #0f5132 0%, #198754 100%);
            min-height: 100vh;
        }
        .card {
            border-radius: 1rem;
        }
        .phase {
            display: none;
        }
        .phase.active {
            display: block;
        }
        .btn-success {
            background-color: #198754;
            border: none;
        }
        .btn-success:hover {
            background-color: #157347;
        }
        .progress {
            height: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">

<div class="card shadow p-4" style="width: 430px;">
    <h3 class="text-center fw-bold mb-1">Create Your B-Cash Account</h3>
    <p class="text-center text-muted mb-3">Fast, secure, and easy sign-up</p>

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="progress">
        <div id="progressBar" class="progress-bar bg-success" style="width: 20%;"></div>
    </div>

    <form id="registerForm" method="POST" action="{{ route('register.post') }}" enctype="multipart/form-data">
        @csrf

        {{-- PHASE 1: Basic Info --}}
        <div class="phase active" id="phase1">
            <div class="mb-3">
                <label class="form-label fw-semibold">Full Name</label>
                <input type="text" name="full_name" class="form-control" placeholder="Juan Dela Cruz" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="you@email.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Phone Number</label>
                <input type="text" name="phone_number" class="form-control" placeholder="09XXXXXXXXX" required>
            </div>
            <button type="button" class="btn btn-success w-100" onclick="nextPhase(2)">Next</button>
        </div>

        {{-- PHASE 2: Personal Details --}}
        <div class="phase" id="phase2">
            <div class="mb-3">
                <label class="form-label fw-semibold">Birthdate</label>
                <input type="date" name="birthdate" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Address</label>
                <input type="text" name="address" class="form-control" placeholder="Street, City, Province" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Gender</label>
                <select name="gender" class="form-select" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" onclick="prevPhase(1)">Back</button>
                <button type="button" class="btn btn-success" onclick="nextPhase(3)">Next</button>
            </div>
        </div>

        {{-- PHASE 3: Valid ID --}}
        <div class="phase" id="phase3">
            <div class="mb-3">
                <label class="form-label fw-semibold">Upload Valid ID</label>
                <input type="file" name="valid_id" class="form-control" accept="image/*" required>
            </div>
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" onclick="prevPhase(2)">Back</button>
                <button type="button" class="btn btn-success" onclick="nextPhase(4)">Next</button>
            </div>
        </div>

        {{-- PHASE 4: Face Verification --}}
        <div class="phase" id="phase4">
            <div class="text-center mb-3">
                <video id="cameraPreview" width="100%" height="auto" autoplay playsinline class="rounded border"></video>
            </div>
            <p class="text-muted small text-center">Please ensure your face is clearly visible.</p>
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" onclick="prevPhase(3)">Back</button>
                <button type="button" class="btn btn-success" onclick="nextPhase(5)">Next</button>
            </div>
        </div>

        {{-- PHASE 5: Password & PIN --}}
        <div class="phase" id="phase5">
            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">4-Digit PIN</label>
                <input type="password" name="pin" class="form-control" maxlength="4" pattern="\d{4}" placeholder="1234" required>
            </div>
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" onclick="prevPhase(4)">Back</button>
                <button type="submit" class="btn btn-success">Register</button>
            </div>
        </div>
    </form>

    <p class="text-center mt-3">
        Already have an account? <a href="{{ route('login') }}" class="text-success text-decoration-none fw-semibold">Login</a>
    </p>
</div>

<script>
    let currentPhase = 1;
    const totalPhases = 5;

    function nextPhase(next) {
        if (next > totalPhases) return;
        document.getElementById('phase' + currentPhase).classList.remove('active');
        document.getElementById('phase' + next).classList.add('active');
        currentPhase = next;
        updateProgress();
    }

    function prevPhase(prev) {
        if (prev < 1) return;
        document.getElementById('phase' + currentPhase).classList.remove('active');
        document.getElementById('phase' + prev).classList.add('active');
        currentPhase = prev;
        updateProgress();
    }

    function updateProgress() {
        const percentage = (currentPhase / totalPhases) * 100;
        document.getElementById('progressBar').style.width = percentage + '%';
    }

    // ✅ Activate camera for face verification
    document.addEventListener('DOMContentLoaded', () => {
        const video = document.getElementById('cameraPreview');
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => { video.srcObject = stream; })
                .catch(() => console.log("Camera access denied or unavailable."));
        }
    });
</script>

</body>
</html>
