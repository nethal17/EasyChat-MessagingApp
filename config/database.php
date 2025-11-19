<?php

// Load environment helper if not already loaded
if (!function_exists('env')) {
    require_once __DIR__ . '/config.php';
}

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct() {
        // Load from environment variables
        $this->host = env('DB_HOST', 'localhost');
        $this->db_name = env('DB_NAME', 'message_app');
        $this->username = env('DB_USER', 'root');
        $this->password = env('DB_PASS', '');
    }

    // Get database connection
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }
}
