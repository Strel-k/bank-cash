<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Verification.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/SessionHelper.php';
require_once __DIR__ . '/../services/IDVerificationService.php';
require_once __DIR__ . '/../services/FaceRecognitionService.php';

class AuthController {
    private $userModel;
    private $verificationModel;
    private $idVerificationService;
    private $faceRecognitionService;
    
    public function __construct() {
        $this->userModel = new User();
        $this->verificationModel = new Verification();
        $this->idVerificationService = new IDVerificationService();
        $this->faceRecognitionService = new FaceRecognitionService();

        // Session is now handled by SessionHelper
    }
    
    public function register() {
        // Start output buffering to prevent any unwanted output
        ob_start();
        
        // Set error reporting to prevent warnings from breaking JSON
        error_reporting(0);
        ini_set('display_errors', 0);
        
        // Initialize all variables
        $phone_number = null;
        $email = null;
        $full_name = null;
        $password = null;
        $confirm_password = null;
        $birthdate = null;
        $address = null;
        $gender = null;
        $pin = null;
        $id_type = null;
        $registration_step = 1;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::error('Method not allowed', 405);
        }
        
        try {
            // Handle both JSON and form data
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'application/json') !== false) {
                $rawData = file_get_contents('php://input');
                $data = json_decode($rawData, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Response::error('Invalid JSON data provided');
                }
            } else {
                $data = $_POST;
            }

            // Get the registration step from request
            $registration_step = isset($data['registration_step']) ? (int)$data['registration_step'] : 1;
            
            // Debugging: Log incoming request data
            error_log("Incoming request data: " . json_encode($data)); // Debugging line
            
            // Validate required fields
            if (!isset($data['phone_number']) || !isset($data['password'])) {
                Response::error('Phone number and password are required');
            }
            
            $phone_number = trim($data['phone_number']);
            $email = isset($data['email']) ? trim($data['email']) : null;
            $full_name = isset($data['full_name']) ? trim($data['full_name']) : '';
            $password = $data['password'];
            $confirm_password = isset($data['confirm_password']) ? $data['confirm_password'] : null;
            $pin = isset($data['pin']) ? trim($data['pin']) : null;
            $birthdate = isset($data['birthdate']) ? trim($data['birthdate']) : null;
            $address = isset($data['address']) ? trim($data['address']) : null;
            $gender = isset($data['gender']) ? trim($data['gender']) : null;
            $id_type = isset($data['id_type']) ? trim($data['id_type']) : null;
            $registration_step = isset($data['registration_step']) ? intval($data['registration_step']) : 1;
            
            // Validate password confirmation
            if ($confirm_password !== null && $password !== $confirm_password) {
                Response::error('Passwords do not match');
            }
            
            // Validate phone number
            if (!preg_match('/^09\d{9}$/', $phone_number)) {
                Response::error('Invalid phone number format. Must be 09XXXXXXXXX');
            }
            
            // Validate email if provided
            if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Response::error('Invalid email format');
            }
            
            // Validate password
            if (strlen($password) < 6) {
                Response::error('Password must be at least 6 characters');
            }
            
            // Validate birthdate if provided
            if ($birthdate) {
                $birthdateObj = DateTime::createFromFormat('Y-m-d', $birthdate);
                if (!$birthdateObj || $birthdateObj->format('Y-m-d') !== $birthdate) {
                    Response::error('Invalid birthdate format');
                }
                
                // Check if user is at least 18 years old
                $today = new DateTime();
                $age = $today->diff($birthdateObj)->y;
                if ($age < 18) {
                    Response::error('You must be at least 18 years old to register');
                }
            }
            
            // Validate gender if provided
            if ($gender && !in_array($gender, ['male', 'female', 'other'])) {
                Response::error('Invalid gender selection');
            }

            // Validate PIN if provided
            if ($pin !== null) {
                if (!preg_match('/^\d{6}$/', $pin)) {
                    Response::error('PIN must be exactly 6 digits');
                }
            }

            // Validate ID type if provided
            if ($id_type !== null) {
                $validIdTypes = ['national_id', 'passport', 'drivers_license'];
                if (!in_array($id_type, $validIdTypes)) {
                    Response::error('Invalid ID type. Must be one of: national_id, passport, drivers_license');
                }
            }

            // Validate registration step
            if ($registration_step < 1 || $registration_step > 4) {
                Response::error('Invalid registration step');
            }

            // Handle file uploads for ID documents and face image if present in step 2 or 3
            if ($registration_step >= 2) {
                $uploadDir = __DIR__ . '/../../storage/app/public/documents/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if (isset($_FILES['id_front'])) {
                    $id_front = $this->handleFileUpload($_FILES['id_front'], $uploadDir, ['image/jpeg', 'image/png', 'image/jpg']);
                    if (!$id_front['success']) {
                        Response::error($id_front['message']);
                    }
                    $data['id_front'] = $id_front['path'];
                }

                if (isset($_FILES['id_back'])) {
                    $id_back = $this->handleFileUpload($_FILES['id_back'], $uploadDir, ['image/jpeg', 'image/png', 'image/jpg']);
                    if (!$id_back['success']) {
                        Response::error($id_back['message']);
                    }
                    $data['id_back'] = $id_back['path'];
                }
            }

            if ($registration_step >= 3) {
                if (isset($_FILES['face_image'])) {
                    $face_image = $this->handleFileUpload($_FILES['face_image'], $uploadDir, ['image/jpeg', 'image/png', 'image/jpg']);
                    if (!$face_image['success']) {
                        Response::error($face_image['message']);
                    }
                    $data['face_image'] = $face_image['path'];
                }
            }
            
            // Extract verification data if provided
            $document_type = isset($data['document_type']) ? trim($data['document_type']) : null;
            $document_number = isset($data['document_number']) ? trim($data['document_number']) : null;
            
            $result = $this->userModel->register(
                $phone_number,
                $email,
                $full_name,
                $password,
                $birthdate,
                $address,
                $gender,
                $pin,
                $id_type,
                isset($data['id_front']) ? $data['id_front'] : null,
                isset($data['id_back']) ? $data['id_back'] : null,
                isset($data['face_image']) ? $data['face_image'] : null,
                $registration_step
            );
            
            if ($result['success']) {
                $userId = $result['user_id'];
                
                // Set session variables for authentication on first step only
                if ($registration_step === 1) {
                    SessionHelper::setUserSession($userId, [
                        'full_name' => $full_name
                    ]);
                }
                
                // Return appropriate response based on step
                $response = [
                    'success' => true,
                    'user_id' => $userId,
                    'step' => $registration_step,
                    'next_step' => $registration_step < 4 ? $registration_step + 1 : null
                ];
                
                switch ($registration_step) {
                    case 1:
                        Response::success($response, 'Basic information saved. Please upload your ID documents.');
                        break;
                    case 2:
                        Response::success($response, 'ID documents uploaded. Please proceed with face verification.');
                        break;
                    case 3:
                        Response::success($response, 'Face verification complete. Please set up your security information.');
                        break;
                    case 4:
                        Response::success($response, 'Registration completed successfully!');
                        break;
                }
                
                // Always create a verification request for document uploads
                $verificationResult = $this->startVerificationProcess(
                    $userId, 
                    $document_type ?: 'national_id', // Default to national_id if not provided
                    $document_number ?: 'PENDING'    // Default placeholder if not provided
                );
                
                error_log("AuthController: User registered with ID: $userId");
                error_log("AuthController: Verification result: " . json_encode($verificationResult));
                
                if ($verificationResult['success']) {
                    error_log("AuthController: Verification created successfully with ID: " . $verificationResult['verification_id']);
                    Response::success([
                        'user_id' => $userId,
                        'verification_id' => $verificationResult['verification_id']
                    ], 'Registration successful. Please complete verification.');
                } else {
                    error_log("AuthController: Verification creation failed: " . $verificationResult['message']);
                    
                    // Try to create verification directly as fallback
                    try {
                        $directResult = $this->verificationModel->createVerificationRequest(
                            $userId,
                            $document_type ?: 'national_id',
                            $document_number ?: 'PENDING'
                        );
                        
                        if ($directResult['success']) {
                            error_log("AuthController: Direct verification creation successful with ID: " . $directResult['verification_id']);
                            Response::success([
                                'user_id' => $userId,
                                'verification_id' => $directResult['verification_id']
                            ], 'Registration successful. Please complete verification.');
                        } else {
                            error_log("AuthController: Direct verification creation also failed: " . $directResult['message']);
                            Response::success([
                                'user_id' => $userId,
                                'verification_error' => $directResult['message']
                            ], 'Registration successful but verification setup failed. Please complete verification later.');
                        }
                    } catch (Exception $e) {
                        error_log("AuthController: Exception in direct verification creation: " . $e->getMessage());
                        Response::success([
                            'user_id' => $userId,
                            'verification_error' => 'Could not create verification record'
                        ], 'Registration successful but verification setup failed. Please complete verification later.');
                    }
                }
            } else {
                Response::error($result['message']);
            }
            
        } catch (Exception $e) {
            Response::error('An unexpected error occurred. Please try again.');
        }
    }
    
    public function login() {
        try {
            error_log("AuthController::login - Starting login process");
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                error_log("AuthController::login - Invalid method: " . $_SERVER['REQUEST_METHOD']);
                Response::error('Method not allowed', 405);
                return;
            }

            $data = json_decode(file_get_contents('php://input'), true);
            error_log("AuthController::login - Received data: " . print_r($data, true));

            if (!isset($data['phone_number']) || !isset($data['password'])) {
                error_log("AuthController::login - Missing required fields");
                Response::error('Phone number and password are required');
                return;
            }

            $phone_number = trim($data['phone_number']);
            $password = $data['password'];

            error_log("AuthController::login - Attempting login for phone: " . $phone_number);
            
            $result = $this->userModel->login($phone_number, $password);
            
            if ($result['success']) {
                // Start a fresh session
                if (session_status() === PHP_SESSION_ACTIVE) {
                    session_regenerate_id(true);
                }
                
                $user = $result['user'];
                
                // Set user session data
                SessionHelper::setUserSession($user['id'], [
                    'full_name' => $user['full_name'],
                    'is_admin' => $user['is_admin']
                ]);

                // Redirect to appropriate dashboard
                $redirectUrl = $user['is_admin'] ? '/admin/dashboard' : '/dashboard';
                header('Location: ' . $redirectUrl);
                exit();
                
                // Return success response with redirect URL and token
                Response::success([
                    'redirect' => $isAdmin ? '/admin/dashboard' : '/dashboard',
                    'user' => [
                        'id' => $user['id'],
                        'full_name' => $user['full_name'],
                        'is_verified' => $user['is_verified'] ?? false,
                        'is_admin' => $isAdmin
                    ],
                    'token' => session_id()
                ]);
            } else {
                // Handle rate limiting with appropriate HTTP status
                $statusCode = isset($result['rate_limited']) && $result['rate_limited'] ? 429 : 401;
                Response::error($result['message'], $statusCode);
            }
        } catch (Exception $e) {
            error_log("AuthController::login - Error: " . $e->getMessage());
            Response::error('Login failed. Please try again.');
        }
    }
    
    public function logout() {
        SessionHelper::clearUserSession();
        Response::success([], 'Logged out successfully');
    }
    
    /**
     * Start verification process for a user
     * @param int $userId
     * @param string $documentType
     * @param string $documentNumber
     * @return array
     */
    private function startVerificationProcess($userId, $documentType, $documentNumber) {
        try {
            // Validate document type
            $validTypes = ['passport', 'drivers_license', 'national_id', 'other'];
            if (!in_array($documentType, $validTypes)) {
                return ['success' => false, 'message' => 'Invalid document type'];
            }
            
            // Create verification request
            $result = $this->verificationModel->createVerificationRequest(
                $userId,
                $documentType,
                $documentNumber
            );
            
            if ($result['success']) {
                // Log the verification start
                $this->verificationModel->logVerificationAction(
                    $userId,
                    'verification_started',
                    [
                        'document_type' => $documentType,
                        'document_number' => $documentNumber
                    ]
                );
                
                return [
                    'success' => true,
                    'verification_id' => $result['verification_id'],
                    'message' => 'Verification process started successfully'
                ];
            } else {
                return ['success' => false, 'message' => $result['message']];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to start verification process'];
        }
    }
    
    public function checkAuth() {
        // For cross-origin requests, we need to be more lenient with session validation
        $userId = SessionHelper::getCurrentUserId();
        if (!$userId) {
            Response::unauthorized('Authentication required');
        }

        Response::success([
            'user_id' => $userId,
            'full_name' => isset($_SESSION['full_name']) ? $_SESSION['full_name'] : ''
        ]);
    }

    /**
     * Handle file upload with validation
     * @param array $file The uploaded file from $_FILES
     * @param string $uploadDir The directory to upload to
     * @param array $allowedMimes Array of allowed mime types
     * @return array Success status and file path or error message
     */
    private function handleFileUpload($file, $uploadDir, $allowedMimes) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'File upload failed'];
        }

        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'File size must be less than 5MB'];
        }

        // Validate mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes)) {
            return ['success' => false, 'message' => 'Invalid file type'];
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Move file to upload directory
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'message' => 'Failed to save file'];
        }

        return [
            'success' => true,
            'path' => 'documents/' . $filename // Return relative path for database storage
        ];
    }
}
?>
