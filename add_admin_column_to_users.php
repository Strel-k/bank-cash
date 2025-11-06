<?php
require_once 'app/config/Database.php';

try {
    $database = new Database();
    $db = $database->connect();

    $query = "ALTER TABLE users ADD COLUMN is_admin BOOLEAN DEFAULT FALSE AFTER is_verified";

    $db->exec($query);

    echo "Column 'is_admin' added to users table successfully.\n";
} catch (PDOException $e) {
    echo "Error adding 'is_admin' column: " . $e->getMessage() . "\n";
}
?>
