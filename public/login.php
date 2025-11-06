<?php
require_once __DIR__ . '/../app/helpers/SessionHelper.php';
require_once __DIR__ . '/../app/helpers/CorsHelper.php';
require_once __DIR__ . '/../app/config/Config.php';

// Initialize session and CORS
SessionHelper::configureSession();
CorsHelper::handleCors();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - B-Cash</title>
<link rel="stylesheet" href="../css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-wallet"></i> B-Cash
            </div>

        </div>
    </header>

    <main>
        <div class="form-container">
            <div class="card">
                <div class="card-header text-center">
                    <i class="fas fa-user-circle" style="font-size: 48px; color: rgba(255, 255, 255, 0.9); margin-bottom: var(--spacing-md);"></i>
                    <h1 class="card-title">Welcome Back</h1>
                    <p style="color: rgba(255, 255, 255, 0.9); font-weight: 500;">Sign in to your B-Cash account</p>
                </div>
                
                <!-- Google Login Button -->
                <div class="form-group">
                    <a href="/b-cash-laravel/public/auth/google" class="google-login-btn" style="display: block; text-decoration: none; background: #4285f4; color: white; padding: 12px 24px; border-radius: 8px; text-align: center; margin-bottom: 20px; transition: background-color 0.3s;">
                        <i class="fab fa-google" style="margin-right: 8px;"></i> Continue with Google
                    </a>
                </div>

                <div style="text-align: center; margin: 20px 0; color: rgba(255, 255, 255, 0.6);">
                    <span>or</span>
                </div>

                <form id="loginForm" class="login-form" method="POST" action="javascript:void(0);">
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
                        <label class="form-label" for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <input type="password" 
                               class="form-input" 
                               name="password" 
                               placeholder="Enter your password" 
                               required>
                    </div>
                    
                    <button type="submit" class="form-button">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>
                
                <div class="text-center" style="margin-top: var(--spacing-lg);">
                    <p style="color: #666;">Don't have an account?</p>
                    <a href="register.php" style="color: var(--gcash-blue); text-decoration: none; font-weight: 600;">
                        <i class="fas fa-user-plus"></i> Create Account
                    </a>
                </div>
                
                <div class="text-center" style="margin-top: var(--spacing-md);">
                    <a href="#" style="color: #666; font-size: var(--font-size-sm); text-decoration: none;">
                        <i class="fas fa-question-circle"></i> Need help?
                    </a>
                </div>
            </div>
        </div>
    </main>

<script src="js/auth.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('loginForm');
        if (!loginForm) {
            console.error('Login form not found');
        }

        // Add hover effect for Google login button
        const googleBtn = document.querySelector('.google-login-btn');
        if (googleBtn) {
            googleBtn.addEventListener('mouseover', function() {
                this.style.background = '#3367d6';
            });
            googleBtn.addEventListener('mouseout', function() {
                this.style.background = '#4285f4';
            });
        }
    });
</script>
</body>
</html>
