<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'B-Cash') }}</title>

    <!-- External CSS (fonts/icons) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Ensure verification.css is loaded before Vite CSS -->
    <link rel="stylesheet" href="{{ asset('css/verification.css') }}">
    
    <!-- Vite / Built CSS -->
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/css/styles.css'])
    @elseif (app()->environment('local'))
        @vite(['resources/css/app.css', 'resources/css/styles.css'])
    @else
        <style>
            /* Minimal inline styles as a final fallback */
            body { font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; margin: 0; padding: 20px; }
            .container { max-width: 800px; margin: 0 auto; background: #fff; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
            .auth-header { padding: 30px; text-align: center; }
            .auth-header h1 { font-size: 2.5rem; margin: 0 0 10px; }
            a.button { display: inline-block; padding: 12px 24px; background: #2563eb; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; }
        </style>
    @endif
    
    @stack('styles')
</head>
<body>
    @yield('content')

    <!-- Loading overlay for async operations -->
    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-spinner">
            <i class="fas fa-circle-notch fa-spin"></i>
            <p>Processing...</p>
        </div>
    </div>

    <!-- Success/Error notification container -->
    <div id="notification" class="notification" style="display: none;"></div>

    <!-- Vite / Built JS -->
    @vite(['resources/js/app.jsx'])
    
    @stack('scripts')
</body>
</html>
