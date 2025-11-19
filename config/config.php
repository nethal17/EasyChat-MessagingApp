<?php

// Load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Set as environment variable
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// Load .env file
loadEnv(dirname(__DIR__) . '/.env');

// Helper function to get environment variable with default
function env($key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/public/uploads/');
define('PROFILE_UPLOAD_PATH', UPLOAD_PATH . 'profiles/');
define('MESSAGE_UPLOAD_PATH', UPLOAD_PATH . 'messages/');

// Define URLs from environment variables
define('BASE_URL', env('BASE_URL', 'http://localhost:8000'));
define('UPLOAD_URL', '/public/uploads/');

// Upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Pagination
define('MESSAGES_PER_PAGE', 50);

// Auto-refresh interval (milliseconds)
define('MESSAGE_REFRESH_INTERVAL', 3000);

// Timezone
date_default_timezone_set('UTC');

// Check if user is logged in(return bool)
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user ID(@return int|null)
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Redirect to a page
function redirect($page) {
    header("Location: " . BASE_URL . "/" . $page);
    exit();
}

// Sanitize input data (for display - adds HTML encoding)
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Clean input data (for storage - removes tags and trims, but doesn't encode)
function cleanInput($data) {
    return strip_tags(trim($data));
}
