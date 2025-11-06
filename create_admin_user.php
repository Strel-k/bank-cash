<?php
// Script to create an admin user
require_once 'app/config/Database.php';
require_once 'app/models/User.php';

// Database connection
$database = new Database();
$db = $database->connect();

// Admin user details
$phone_number = '09999999999';
$email = 'admin@bcash.com';
$full_name = 'System Administrator';
$password = 'admin123';
$birthdate = '1990-01-01';
$address = 'Admin Office';
$gender = 'other';

try {
    // Hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert admin user
    $stmt = $db->prepare("
        INSERT INTO users (phone_number, email, full_name, birthdate, address, gender, password_hash, is_admin, is_verified) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 1, 1)
    ");
    
    $stmt->execute([$phone_number, $email, $full_name, $birthdate, $address, $gender, $password_hash]);
    
    $user_id = $db->lastInsertId();
    
    // Create wallet for admin user
    $account_number = 'BC' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    $stmt = $db->prepare("
        INSERT INTO wallets (user_id, account_number, balance) 
        VALUES (?, ?, 10000.00)
    ");
    $stmt->execute([$user_id, $account_number]);
    
    echo "Admin user created successfully!\n";
    echo "User ID: $user_id\n";
    echo "Phone Number: $phone_number\n";
    echo "Email: $email\n";
    echo "Password: $password\n";
    echo "Account Number: $account_number\n";
    echo "Initial Balance: ₱10,000.00\n";
    echo "\nYou can now login with these credentials to access the admin panel.\n";
    
} catch(PDOException $e) {
    echo "Error creating admin user: " . $e->getMessage() . "\n";
}
?>