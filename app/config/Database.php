<?php
require_once 'Config.php';

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $connection;
    
    public function __construct() {
        $this->host = Config::DB_HOST;
        $this->db_name = Config::DB_NAME;
        $this->username = Config::DB_USER;
        $this->password = Config::DB_PASS;
    }
    
    public function connect() {
        $this->connection = null;
        
        try {
            $this->connection = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch(PDOException $e) {
            // Return null instead of echoing to prevent breaking JSON responses
            error_log("Database connection error: " . $e->getMessage());
            return null;
        }
        
        return $this->connection;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}
?>
