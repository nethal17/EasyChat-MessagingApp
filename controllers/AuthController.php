<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    // Handle user registration
    public function register() {
        // Validate input
        $errors = [];

        if (empty($_POST['name'])) {
            $errors[] = "Name is required";
        }

        if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required";
        }

        if (empty($_POST['phone'])) {
            $errors[] = "Phone number is required";
        }

        if (empty($_POST['phone']) || !preg_match('/^[0-9]{10}$/', $_POST['phone'])) {
            $errors[] = "Valid phone number is required (10 digits)";
        }

        if (empty($_POST['password'])) {
            $errors[] = "Password is required";
        } else {
            $password = $_POST['password'];
            
            // Check minimum length
            if (strlen($password) < 6) {
                $errors[] = "Password must be at least 6 characters";
            }
            
            // Check for uppercase letter
            if (!preg_match('/[A-Z]/', $password)) {
                $errors[] = "Password must contain at least one uppercase letter";
            }
            
            // Check for lowercase letter
            if (!preg_match('/[a-z]/', $password)) {
                $errors[] = "Password must contain at least one lowercase letter";
            }
            
            // Check for number
            if (!preg_match('/[0-9]/', $password)) {
                $errors[] = "Password must contain at least one number";
            }
            
            // Check for special character
            if (!preg_match('/[!@#$%^&*()?,."{}\/|<>:;\'`~=\[\]\-\+]/', $password)) {
                $errors[] = "Password must contain at least one special character";
            }
        }

        if ($_POST['password'] !== $_POST['confirm_password']) {
            $errors[] = "Passwords do not match";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Check if email already exists
        $this->user->email = sanitize($_POST['email']);
        if ($this->user->emailExists()) {
            return ['success' => false, 'errors' => ['Email already registered']];
        }

        // Create user
        $this->user->name = sanitize($_POST['name']);
        $this->user->phone = sanitize($_POST['phone'] ?? '');
        $this->user->password = $_POST['password'];

        $userId = $this->user->register();

        if ($userId) {
            // Auto login after registration
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $this->user->name;
            $_SESSION['user_email'] = $this->user->email;

            return ['success' => true, 'message' => 'Registration successful'];
        }

        return ['success' => false, 'errors' => ['Registration failed']];
    }

    // Handle user login
    public function login() {
        // Validate input
        $errors = [];

        if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required";
        }

        if (empty($_POST['password'])) {
            $errors[] = "Password is required";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Attempt login
        $this->user->email = sanitize($_POST['email']);
        $this->user->password = $_POST['password'];

        $userData = $this->user->login();

        if ($userData) {
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['user_name'] = $userData['name'];
            $_SESSION['user_email'] = $userData['email'];
            $_SESSION['user_picture'] = $userData['profile_picture'];

            return ['success' => true, 'message' => 'Login successful'];
        }

        return ['success' => false, 'errors' => ['Invalid email or password']];
    }

    // Handle user logout
    public function logout() {
        session_destroy();
        redirect('index.php');
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $controller = new AuthController();

    switch ($_POST['action']) {
        case 'register':
            echo json_encode($controller->register());
            break;
        case 'login':
            echo json_encode($controller->login());
            break;
    }
    exit();
}
