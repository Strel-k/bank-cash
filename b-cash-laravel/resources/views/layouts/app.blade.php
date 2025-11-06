<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'B-Cash - Your Digital Wallet')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/css/styles.css', 'resources/css/verification.css', 'resources/css/header.css'])
    
    <!-- External CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @stack('styles')
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-wallet"></i> B-Cash
            </div>
            @auth
            <nav class="nav-links">
                <div class="user-menu">
                    <span class="user-info">
                        <i class="fas fa-user-circle"></i>
                        <span class="user-name">{{ auth()->user()->full_name }}</span>
                        @if(auth()->user()->is_admin)
                            <span class="admin-badge">Admin</span>
                        @endif
                    </span>
                    <div class="user-actions">
                        @if(auth()->user()->is_admin)
                            <a href="/admin" class="nav-link">
                                <i class="fas fa-shield-alt"></i> Admin Panel
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="logout-button">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </nav>
            @endauth
        </div>
    </header>

    <main class="container">
        @yield('content')
    </main>

    @stack('modals')
    
    <!-- Scripts -->
    <script>
        window.csrfToken = '{{ csrf_token() }}';
        window.userId = '{{ auth()->id() }}';
        
        // Theme Toggle
        function initTheme() {
            const isDark = localStorage.getItem('theme') === 'dark';
            document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
        }
        
        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }
        
        initTheme();
    </script>
    @stack('scripts')
</body>
</html>