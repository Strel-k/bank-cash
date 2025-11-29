# Authentication Fix - 500 Internal Server Error Resolution

## Problem Identified
- JavaScript client was not sending Bearer tokens in API requests
- Laravel Sanctum requires Bearer token or session cookie authentication
- API routes use `auth:sanctum` middleware but client wasn't providing proper auth

## Changes Made

### 1. Updated `public/js/dashboard.js`
- Added `getToken()` method to retrieve token from sessionStorage or global variable
- Added `setToken()` method to store token
- Modified `getAuthHeaders()` to include `Authorization: Bearer ${token}` header when token is available
- **Fixed API base URL**: Changed from `/api` to `http://127.0.0.1:8000/api` to avoid CORS issues
- **Fixed CSRF URL**: Changed from `/sanctum/csrf-cookie` to `http://127.0.0.1:8000/sanctum/csrf-cookie`

### 2. Updated `resources/views/dashboard/dashboard.blade.php`
- Added `window.apiToken = '{{ session("api_token") }}';` to make token available to JavaScript

## Testing Required

### Manual Testing Steps:
1. Start Laravel server: `php artisan serve --host=127.0.0.1 --port=8000`
2. Open browser to `http://127.0.0.1:8000/login`
3. Login with valid credentials
4. Navigate to dashboard
5. Check browser console for authentication errors
6. Test API calls (balance, transactions, etc.) - should no longer return 500 errors

### API Endpoints to Test:
- GET `/api/wallet/balance`
- GET `/api/transactions/history`
- POST `/api/wallet/add-money`
- POST `/api/wallet/send-money`

### Expected Results:
- No more 500 Internal Server Error
- API responses should return proper JSON with success/error messages
- Dashboard should load balance and transaction history

## Notes
- Server is currently running on port 8000
- Sanctum CSRF initialization is still in place for additional security
- Token is stored in sessionStorage for persistence across page reloads
- **CORS Issue Fixed**: Changed relative URLs to absolute URLs to prevent CORS errors when JavaScript makes requests to the API
- **URL Correction**: Updated API base URL from `http://127.0.0.1:8000/api` to `http://localhost:8000/api` to match the browser's origin
- **Sanctum Configuration Fixed**: Updated `config/sanctum.php` to explicitly include `localhost:8000` in stateful domains to ensure proper cookie handling
- **Sanctum Middleware Added**: Added `auth.sanctum` middleware alias to `app/Http/Kernel.php` to enable Sanctum authentication middleware
