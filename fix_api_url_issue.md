# Fix for API URL 400/404 Error

## Problem Identified
The JavaScript frontend is running on `localhost:3000` but trying to access the PHP API at `http://localhost:3000/public/api/wallet.php`, which doesn't exist because:

1. The PHP server (XAMPP) is running on port 80 (default HTTP port)
2. The frontend is running on port 3000 (likely a development server)
3. The API endpoints are returning 404 because the URL path is incorrect

## Solutions

### Solution 1: Fix the JavaScript API URL (Recommended)

Update the `public/js/wallet.js` file to use the correct API URL:

```javascript
// Change this line in wallet.js:
this.apiUrl = 'api/wallet.php';

// To one of these (test which one works):
this.apiUrl = 'http://localhost/B-Cash%20AJAX/public/api/wallet.php';
// OR
this.apiUrl = 'http://localhost:80/B-Cash%20AJAX/public/api/wallet.php';
// OR (if XAMPP is configured with a virtual host)
this.apiUrl = 'http://localhost/public/api/wallet.php';
```

### Solution 2: Set up XAMPP Virtual Host

1. Edit `C:\xampp\apache\conf\extra\httpd-vhosts.conf`
2. Add this virtual host configuration:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/Users/Strelok/Documents/PHP - Laravel/B-Cash AJAX/public"
    ServerName bcash.local
    <Directory "C:/Users/Strelok/Documents/PHP - Laravel/B-Cash AJAX/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

3. Edit `C:\Windows\System32\drivers\etc\hosts` (as Administrator)
4. Add: `127.0.0.1 bcash.local`
5. Restart Apache
6. Update JavaScript to use: `this.apiUrl = 'http://bcash.local/api/wallet.php';`

### Solution 3: Move Files to XAMPP htdocs

1. Copy the entire project to `C:\xampp\htdocs\bcash\`
2. Update JavaScript to use: `this.apiUrl = 'http://localhost/bcash/public/api/wallet.php';`

### Solution 4: Use Relative URLs (If frontend and backend are on same server)

If you serve the frontend from the same Apache server:
```javascript
this.apiUrl = '/public/api/wallet.php'; // Relative URL
```

## Testing the Fix

1. First, test if the API is accessible by visiting one of these URLs in your browser:
   - `http://localhost/B-Cash%20AJAX/public/test_api_access.php`
   - `http://localhost:80/B-Cash%20AJAX/public/test_api_access.php`

2. If you get a JSON response, use that base URL in your JavaScript

3. Test the addMoney API specifically:
   - Use the debug HTML file: `debug_addmoney_request.html`
   - Update the fetch URL in that file to use the correct base URL

## Current Status

- ✅ Database transaction issue fixed
- ✅ Config.php include issue fixed  
- ✅ WalletController addMoney method working correctly
- ❌ URL/CORS issue preventing frontend from reaching API

The 400 Bad Request error is actually a 404 Not Found error being misreported by the browser because the API endpoint URL is incorrect.