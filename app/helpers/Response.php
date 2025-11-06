<?php
class Response {
    public static function json($data, $status = 200) {
        try {
            // Clear any output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Start fresh buffer
            ob_start();
            
            // Handle CORS first
            require_once __DIR__ . '/CorsHelper.php';
            CorsHelper::handleCors();
            
            // Set response headers
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
            
            // Ensure errors don't break JSON
            error_reporting(E_ALL);
            ini_set('display_errors', 0);
            
            // Send JSON response
            echo json_encode($data);
            ob_end_flush();
            exit();
        } catch (Exception $e) {
            error_log("Response Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
            exit();
        }
    }
    
    public static function success($data = [], $message = 'Success') {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if (is_array($data)) {
            if (isset($data['redirect'])) {
                $response['redirect'] = $data['redirect'];
                unset($data['redirect']);
            }
            if (!empty($data)) {
                $response['data'] = $data;
            }
        } else {
            $response['data'] = $data;
        }

        self::json($response);
    }
    
    public static function error($message = 'Error', $status = 400) {
        self::json([
            'success' => false,
            'message' => $message
        ], $status);
    }
    
    public static function unauthorized($message = 'Unauthorized') {
        self::error($message, 401);
    }
    
    public static function notFound($message = 'Not Found') {
        self::error($message, 404);
    }
}
?>
