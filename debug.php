<?php
// Debug script to identify the 500 error
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug Information</h1>";

// Check PHP version
echo "<h2>PHP Version</h2>";
echo phpversion() . "<br>";

// Check if required files exist
echo "<h2>File Existence Check</h2>";
$files = [
    'app/config/Config.php',
    'app/config/Database.php',
    'app/controllers/AuthController.php',
    'app/models/User.php',
    'app/helpers/Response.php'
];

foreach ($files as $file) {
    echo "$file: " . (file_exists($file) ? "EXISTS" : "MISSING") . "<br>";
}

// Check database connection
echo "<h2>Database Connection</h2>";
try {
    require_once 'app/config/Config.php';
    require_once 'app/config/Database.php';
    
    $db = new Database();
    $conn = $db->connect();
    
    if ($conn) {
        echo "Database connection: SUCCESS<br>";
        
        // Check tables
        $tables = ['users', 'wallets', 'transactions'];
        foreach ($tables as $table) {
            $stmt = $conn->query("SHOW TABLES LIKE '$table'");
            echo "Table '$table': " . ($stmt->rowCount() > 0 ? "EXISTS" : "MISSING") . "<br>";
        }
    } else {
        echo "Database connection: FAILED<br>";
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}

// Check PHP extensions
echo "<h2>PHP Extensions</h2>";
$extensions = ['pdo', 'pdo_mysql', 'json'];
foreach ($extensions as $ext) {
    echo "$ext: " . (extension_loaded($ext) ? "LOADED" : "MISSING") . "<br>";
}
?>
