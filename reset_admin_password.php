<?php
// Script to reset password for admin user
require_once 'app/config/Database.php';

// Database connection
$database = new Database();
$db = $database->connect();

// Admin user ID
$user_id = 38;
$new_password = 'admin123';

try {
    // Hash new password
    $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
    
    // Update password
    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->execute([$password_hash, $user_id]);
    
    if ($stmt->rowCount() > 0) {
        echo "Password reset successfully for admin user (ID: $user_id)!\n";
        echo "New password: $new_password\n";
        echo "\nYou can now login with these credentials to access the admin panel.\n";
    } else {
        echo "Failed to reset password. User not found.\n";
    }
    
} catch(PDOException $e) {
    echo "Error resetting password: " . $e->getMessage() . "\n";
}
?>