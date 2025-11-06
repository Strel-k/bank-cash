@extends('layouts.guest')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="logo">
            <i class="fas fa-wallet"></i> B-Cash
        </div>
        <h1>Login to your account</h1>

        @if ($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="phone_number">
                    <i class="fas fa-phone"></i> Phone Number
                </label>
                <input type="text" 
                       id="phone_number" 
                       name="phone_number" 
                       value="{{ old('phone_number') }}" 
                       pattern="[0-9]{11}" 
                       placeholder="09XX XXX XXXX"
                       class="@error('phone_number') is-invalid @enderror"
                       required 
                       autofocus>
                @error('phone_number')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <div class="password-input-group">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="@error('password') is-invalid @enderror"
                           placeholder="Enter your password"
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
                <label class="remember-me">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit" class="form-button">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>

            <div class="relative flex items-center justify-center my-4">
                <div class="absolute border-t border-gray-300 w-full"></div>
                <div class="relative bg-white px-4 text-sm text-gray-500">
                    or
                </div>
            </div>

            <a href="{{ route('login.google') }}" class="flex items-center justify-center gap-2 w-full px-4 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors text-gray-700 mb-4">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Continue with Google
            </a>

            <div class="auth-footer">
                <p>Don't have an account? <a href="{{ route('register') }}">Register</a></p>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
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

// Format phone number as user types
document.getElementById('phone_number').addEventListener('input', function(e) {
    let value = this.value.replace(/\D/g, '').substring(0, 11);
    if (value.length >= 11) {
        this.value = value;
    }
});
</script>
@endpush