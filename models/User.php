<?php

require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table = "users";

    public $id;
    public $name;
    public $email;
    public $phone;
    public $password;
    public $profile_picture;

    // Constructor
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Register a new user     
    public function register() {
        $query = "INSERT INTO " . $this->table . " 
                  (name, email, phone, password) 
                  VALUES (:name, :email, :phone, :password)";

        $stmt = $this->conn->prepare($query);

        // Hash password
        $hashed_password = password_hash($this->password, PASSWORD_BCRYPT);

        // Bind parameters
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":password", $hashed_password);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Login user
    public function login() {
        $query = "SELECT id, name, email, phone, password, profile_picture 
                  FROM " . $this->table . " 
                  WHERE email = :email 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        $user = $stmt->fetch();

        if ($user && password_verify($this->password, $user['password'])) {
            return $user;
        }
        return false;
    }

    // Check if email already exists
    public function emailExists() {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Get user by ID
    public function getUserById($id) {
        $query = "SELECT id, name, email, phone, profile_picture, created_at 
                  FROM " . $this->table . " 
                  WHERE id = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    // Get user by ID with password (for password verification)
    public function getUserByIdWithPassword($id) {
        $query = "SELECT id, name, email, phone, password, profile_picture, created_at 
                  FROM " . $this->table . " 
                  WHERE id = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    // Update user profile
    public function updateProfile() {
        $query = "UPDATE " . $this->table . " 
                  SET name = :name, email = :email, phone = :phone";
        
        // Add profile picture to query if it's set
        if ($this->profile_picture !== null) {
            $query .= ", profile_picture = :profile_picture";
        }
        
        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":id", $this->id);

        if ($this->profile_picture !== null) {
            $stmt->bindParam(":profile_picture", $this->profile_picture);
        }

        return $stmt->execute();
    }

    // Update user password
    public function updatePassword($userId, $newPasswordHash) {
        $query = "UPDATE " . $this->table . " 
                  SET password = :password 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":password", $newPasswordHash);
        $stmt->bindParam(":id", $userId);

        return $stmt->execute();
    }

    // Get all users except current user
    public function getAllUsersExcept($currentUserId) {
        $query = "SELECT id, name, email, profile_picture 
                  FROM " . $this->table . " 
                  WHERE id != :current_user_id 
                  ORDER BY name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":current_user_id", $currentUserId);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Search users by name or email
    public function searchUsers($searchTerm, $currentUserId) {
        $query = "SELECT id, name, email, profile_picture 
                  FROM " . $this->table . " 
                  WHERE id != :current_user_id 
                  AND (name LIKE :search OR email LIKE :search)
                  ORDER BY name ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":current_user_id", $currentUserId);
        $searchTerm = "%{$searchTerm}%";
        $stmt->bindParam(":search", $searchTerm);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
