<?php
require_once 'app/config/Database.php';

try {
    $database = new Database();
    $db = $database->connect();
    
    // Add additional fields to users table
    $alterQueries = [
        "ALTER TABLE users ADD COLUMN birthdate DATE NULL AFTER full_name",
        "ALTER TABLE users ADD COLUMN address TEXT NULL AFTER birthdate",
        "ALTER TABLE users ADD COLUMN gender ENUM('male', 'female', 'other') NULL AFTER address",
        "ALTER TABLE users ADD COLUMN verification_level ENUM('basic', 'verified', 'premium') DEFAULT 'basic' AFTER is_verified",
        "ALTER TABLE users ADD COLUMN verification_expires_at TIMESTAMP NULL AFTER verification_level"
    ];
    
    foreach ($alterQueries as $query) {
        try {
            $db->exec($query);
            echo "Executed: $query\n";
        } catch (PDOException $e) {
            echo "Error executing '$query': " . $e->getMessage() . "\n";
        }
    }
    
    echo "User table update completed.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
