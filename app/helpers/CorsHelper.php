<?php
class CorsHelper {
    private static $config = null;
    
    private static function loadConfig() {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../config/api.php';
        }
        return self::$config;
    }

    public static function handleCors() {
        // Load config
        $config = self::loadConfig();
        
        // Get and validate origin
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        // Log request info
        error_log("CorsHelper: Incoming request");
        error_log("CorsHelper: Origin: " . $origin);
        error_log("CorsHelper: Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'));
        
        // Set CORS headers if origin is allowed
        if (in_array($origin, $config['allowed_origins'])) {
            // Clear any previous headers
            if (!headers_sent()) {
                header_remove('Access-Control-Allow-Origin');
                header_remove('Access-Control-Allow-Credentials');
                header_remove('Access-Control-Allow-Methods');
                header_remove('Access-Control-Allow-Headers');
            }
            
            // Set new headers
            header("Access-Control-Allow-Origin: {$origin}");
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept");
            header("Access-Control-Max-Age: 3600");
            header("Vary: Origin");
            
            error_log("CorsHelper: CORS headers set successfully");
        } else {
            error_log("CorsHelper: Origin not allowed: " . $origin);
        }
        
        // Handle preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            error_log("CorsHelper: Handling OPTIONS request");
            http_response_code(200);
            header("Content-Length: 0");
            header("Content-Type: text/plain");
            exit();
        }
    }
}
