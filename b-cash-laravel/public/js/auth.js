// Authentication JavaScript for B-Cash - Laravel API version
console.log('Auth.js loaded successfully');

class AuthService {
    constructor() {
        this.apiUrl = '/api/auth'; // Laravel API base URL
    }

    // Ensure CSRF cookie is present for Sanctum (call before stateful requests)
    async ensureCsrf() {
        try {
            await fetch('/sanctum/csrf-cookie', {
                method: 'GET',
                credentials: 'include'
            });
            return true;
        } catch (e) {
            console.warn('Failed to get CSRF cookie', e);
            return false;
        }
    }

    async register(userData) {
        try {
            console.log('AuthService: Starting registration...');
            console.log('AuthService: API URL:', `${this.apiUrl}/register`);
            console.log('AuthService: User data:', JSON.stringify(userData));

            // Ensure CSRF cookie so Laravel accepts the session-based request
            await this.ensureCsrf();

            const response = await fetch(`${this.apiUrl}/register`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : ''
                },
                credentials: 'include',
                body: JSON.stringify(userData)
            });

            console.log('AuthService: Response status:', response.status);
            console.log('AuthService: Response headers:', response.headers);

            const result = await response.json();
            console.log('AuthService: Parsed result:', result);

            if (result.success) {
                console.log('AuthService: Registration successful');
                return { success: true, data: result.data };
            } else {
                console.log('AuthService: Registration failed:', result.message);
                return { success: false, message: result.message };
            }
        } catch (error) {
            console.error('AuthService: Registration error:', error);
            return { success: false, message: 'Registration failed: ' + error.message };
        }
    }
    
    async login(credentials) {
        try {
            console.log('AuthService: Starting login process');
            // Ensure CSRF cookie for Sanctum session auth
            await this.ensureCsrf();

            const requestUrl = `${this.apiUrl}/login`;
            console.log('AuthService: Full request URL:', new URL(requestUrl, window.location.href).href);
            console.log('AuthService: Credentials:', JSON.stringify(credentials));
            
            // First, check if we're already logged in
            if (localStorage.getItem('user_id')) {
                console.log('AuthService: Found existing user_id, clearing it');
                localStorage.removeItem('user_id');
            }
            
            console.log('AuthService: Sending fetch request...');
            const response = await fetch(requestUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : ''
                },
                body: JSON.stringify(credentials),
                credentials: 'include',
                mode: 'cors',
                cache: 'no-cache'
            });
            
            console.log('AuthService: Response received:', {
                status: response.status,
                statusText: response.statusText,
                headers: Object.fromEntries(response.headers.entries()),
                redirected: response.redirected,
                url: response.url
            });

            // If the server redirected (302) and fetch followed it, perform a client-side navigation
            if (response.redirected && response.url) {
                console.log('AuthService: Server redirected to', response.url);
                return { success: true, redirected: true, url: response.url };
            }

            // Try to get response text regardless of status
            const responseText = await response.text();
            console.log('AuthService: Raw response:', responseText);

            if (!response.ok) {
                // If non-OK but response contains HTML, navigate to it
                const contentType = response.headers.get('content-type') || '';
                if (contentType.includes('text/html')) {
                    return { success: true, redirected: true, url: window.location.href };
                }
                throw new Error(`HTTP error! status: ${response.status}, response: ${responseText}`);
            }

            let result = null;
            try {
                result = JSON.parse(responseText);
                console.log('AuthService: Parsed response:', result);
            } catch (e) {
                console.warn('AuthService: Response is not JSON:', e);
            }

            // If server returned JSON with redirect URL, honor it
            if (result && result.success && result.redirect) {
                return { success: true, redirected: true, url: result.redirect, data: result.data || null };
            }

            if (result && result.success) {
                console.log('AuthService: Login successful');
                if (result.data && result.data.user_id) {
                    localStorage.setItem('user_id', result.data.user_id);
                }
                return { success: true, data: result.data };
            }

            if (result) {
                console.log('AuthService: Login failed:', result.message);
                return { success: false, message: result.message };
            }

            // Fallback: if response is HTML, navigate to it
            const contentType = response.headers.get('content-type') || '';
            if (contentType.includes('text/html')) {
                return { success: true, redirected: true, url: response.url || window.location.href };
            }

            return { success: false, message: 'Unknown server response' };
        } catch (error) {
            console.error('AuthService: Login error:', error);
            return { success: false, message: 'Login failed: ' + error.message };
        }
    }
    
    async logout() {
        try {
            await this.ensureCsrf();
            const response = await fetch(`${this.apiUrl}/logout`, {
                method: 'POST',
                credentials: 'include',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '' }
            });
            const result = await response.json();
            
            if (result.success) {
                localStorage.removeItem('b_cash_token');
                return { success: true };
            }
        } catch (error) {
            return { success: false, message: 'Logout failed' };
        }
    }
    
    isLoggedIn() {
        return localStorage.getItem('b_cash_token') !== null;
    }
}

// Initialize auth service
const authService = new AuthService();
window.authService = authService; // Make it globally accessible

// Form handlers
document.addEventListener('DOMContentLoaded', function() {
    // Registration form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const userData = {
                phone_number: formData.get('phone_number'),
                email: formData.get('email'),
                full_name: formData.get('full_name'),
                password: formData.get('password')
            };
            
            const result = await authService.register(userData);
            
            if (result.success) {
                alert('Registration successful!');
                window.location.href = '/login';
            } else {
                alert(result.message);
            }
        });
    }
    
    // Login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault(); // Prevent form from submitting normally
            
            try {
                console.log('Login form submitted');
                
                // Disable form while processing
                const submitButton = this.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                
                const formData = new FormData(this);
                const credentials = {
                    phone_number: formData.get('phone_number'),
                    password: formData.get('password')
                };

                console.log('Login credentials:', credentials);
                
                const result = await authService.login(credentials);
                console.log('Login result:', result);

                if (result.success) {
                    console.log('Login successful, preparing redirect...');

                    // If server indicated a redirect (302 followed by fetch), go there
                    if (result.redirected && result.url) {
                        console.log('Client redirect to', result.url);
                        window.location.href = result.url;
                        return;
                    }

                    // Store any necessary data
                    if (result.data && result.data.user && result.data.user.id) {
                        localStorage.setItem('user_id', result.data.user.id);
                    }

                    // Determine redirect based on user type (fallback)
                    const isAdmin = result.data && result.data.user && result.data.user.is_admin === true;
                    const redirectUrl = isAdmin ? '/admin/dashboard' : '/dashboard';

                    console.log(`Redirecting to ${redirectUrl}...`);
                    window.location.href = redirectUrl;
                } else {
                    console.log('Login failed:', result.message);
                    alert(result.message || 'Login failed. Please try again.');
                    submitButton.disabled = false;
                }
            } catch (error) {
                console.error('Login error:', error);
                alert('Login failed. Please try again.');
                submitButton.disabled = false;
            }
        });
    }
});
