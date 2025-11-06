<?php

class SessionHelper {
    /**
     * Configure session settings for consistent behavior across all API endpoints
     */
    public static function configureSession() {
        error_log("SessionHelper::configureSession - Starting configuration");
        
        // Check if headers were already sent
        if (headers_sent($file, $line)) {
            error_log("SessionHelper::configureSession - Headers already sent in $file on line $line");
        }

        // Check if we're running in CLI mode
        $isCli = (php_sapi_name() === 'cli');
        
        if (!$isCli) {
            // Set session save path and configure session parameters only if session is not active
            if (session_status() !== PHP_SESSION_ACTIVE) {
                $sessionPath = sys_get_temp_dir() . '/php_sessions';
                if (!is_dir($sessionPath)) {
                    mkdir($sessionPath, 0755, true);
                }
                session_save_path($sessionPath);
                error_log("SessionHelper::configureSession - Session save path: " . session_save_path());

                // Configure session parameters
                ini_set('session.use_strict_mode', 1);
                ini_set('session.use_cookies', 1);
                ini_set('session.use_only_cookies', 1);
                ini_set('session.cookie_httponly', 1);

                // Load and apply session configuration
                $config = require __DIR__ . '/../config/session.php';

                // Set session cookie parameters
                session_set_cookie_params([
                    'lifetime' => $config['cookie_lifetime'],
                    'path' => $config['cookie_path'],
                    'domain' => $config['cookie_domain'],
                    'secure' => $config['cookie_secure'],
                    'httponly' => $config['cookie_httponly'],
                    'samesite' => $config['cookie_samesite']
                ]);

                // Set session name
                session_name($config['name']);

                // Set garbage collection lifetime
                ini_set('session.gc_maxlifetime', $config['gc_maxlifetime']);
            }
        }

        // Start session if not active
        if (session_status() !== PHP_SESSION_ACTIVE) {
            error_log("SessionHelper::configureSession - Starting new session");
            session_start();
            error_log("SessionHelper::configureSession - New session ID: " . session_id());
        } else {
            error_log("SessionHelper::configureSession - Using existing session ID: " . session_id());
        }
        
        error_log("SessionHelper::configureSession - Session data: " . print_r($_SESSION, true));
    }

    /**
     * Get current user ID from session
     */
    public static function getCurrentUserId() {
        self::configureSession();
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated() {
        self::configureSession();
        error_log("SessionHelper::isAuthenticated - Checking session: " . session_id());
        error_log("SessionHelper::isAuthenticated - Session data: " . print_r($_SESSION, true));
        return self::getCurrentUserId() !== null;
    }

    /**
     * Set user session data
     */
    public static function setUserSession($userId, $userData = []) {
        self::configureSession();
        
        // Regenerate session ID for security
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        
        $_SESSION['user_id'] = $userId;
        $_SESSION['full_name'] = $userData['full_name'] ?? '';
        $_SESSION['is_admin'] = $userData['is_admin'] ?? false;
        
        error_log("SessionHelper::setUserSession - Session data set: " . print_r($_SESSION, true));
    }

    /**
     * Clear user session
     */
    public static function clearUserSession() {
        self::configureSession();
        session_unset();
        session_destroy();
    }
}
?>
