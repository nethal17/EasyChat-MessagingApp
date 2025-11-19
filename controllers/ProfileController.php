<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

class ProfileController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    // Get user profile
    public function getProfile() {
        if (!isLoggedIn()) {
            return ['success' => false, 'errors' => ['Not authenticated']];
        }

        $userData = $this->user->getUserById(getCurrentUserId());

        if ($userData) {
            return ['success' => true, 'data' => $userData];
        }

        return ['success' => false, 'errors' => ['User not found']];
    }

    // Update user profile
    public function updateProfile() {
        if (!isLoggedIn()) {
            return ['success' => false, 'errors' => ['Not authenticated']];
        }

        $errors = [];

        // Validate input
        if (empty($_POST['name'])) {
            $errors[] = "Name is required";
        }

        if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Set user data
        $this->user->id = getCurrentUserId();
        $this->user->name = sanitize($_POST['name']);
        $this->user->email = sanitize($_POST['email']);
        $this->user->phone = sanitize($_POST['phone'] ?? '');

        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleProfilePictureUpload($_FILES['profile_picture']);
            
            if ($uploadResult['success']) {
                $this->user->profile_picture = $uploadResult['filename'];
            } else {
                return $uploadResult;
            }
        }

        // Update profile
        if ($this->user->updateProfile()) {
            // Update session
            $_SESSION['user_name'] = $this->user->name;
            $_SESSION['user_email'] = $this->user->email;
            if (isset($this->user->profile_picture)) {
                $_SESSION['user_picture'] = $this->user->profile_picture;
            }

            return ['success' => true, 'message' => 'Profile updated successfully'];
        }

        return ['success' => false, 'errors' => ['Failed to update profile']];
    }

    // Handle profile picture upload
    private function handleProfilePictureUpload($file) {
        // Validate file type
        $fileType = mime_content_type($file['tmp_name']);
        if (!in_array($fileType, ALLOWED_IMAGE_TYPES)) {
            return ['success' => false, 'errors' => ['Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed']];
        }

        // Validate file size
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['success' => false, 'errors' => ['File too large. Maximum size is 5MB']];
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . getCurrentUserId() . '_' . time() . '.' . $extension;
        $uploadPath = PROFILE_UPLOAD_PATH . $filename;

        // Create directory if it doesn't exist
        if (!is_dir(PROFILE_UPLOAD_PATH)) {
            mkdir(PROFILE_UPLOAD_PATH, 0755, true);
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Optional: Resize image
            $this->resizeImage($uploadPath, 300, 300);
            
            return ['success' => true, 'filename' => $filename];
        }

        return ['success' => false, 'errors' => ['Failed to upload file']];
    }

    // Resize and compress image
    private function resizeImage($filepath, $maxWidth, $maxHeight) {
        list($width, $height, $type) = getimagesize($filepath);

        // Calculate new dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = intval($width * $ratio);
        $newHeight = intval($height * $ratio);

        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Load source image
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($filepath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($filepath);
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($filepath);
                break;
            case IMAGETYPE_WEBP:
                $source = imagecreatefromwebp($filepath);
                break;
            default:
                return;
        }

        // Resize
        imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save resized image
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($newImage, $filepath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($newImage, $filepath, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($newImage, $filepath);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($newImage, $filepath, 85);
                break;
        }

        imagedestroy($source);
        imagedestroy($newImage);
    }

    // Change password
    public function changePassword() {
        if (!isLoggedIn()) {
            return ['success' => false, 'errors' => ['Not authenticated']];
        }

        $errors = [];

        // Validate input
        if (empty($_POST['current_password'])) {
            $errors[] = "Current password is required";
        }

        if (empty($_POST['new_password'])) {
            $errors[] = "New password is required";
        } elseif (strlen($_POST['new_password']) < 6) {
            $errors[] = "New password must be at least 6 characters long";
        }

        if (empty($_POST['confirm_password'])) {
            $errors[] = "Please confirm your new password";
        }

        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $errors[] = "New passwords do not match";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Get user data with password
        $userData = $this->user->getUserByIdWithPassword(getCurrentUserId());

        if (!$userData) {
            return ['success' => false, 'errors' => ['User not found']];
        }

        // Verify current password
        if (!password_verify($_POST['current_password'], $userData['password'])) {
            return ['success' => false, 'errors' => ['Current password is incorrect']];
        }

        // Check if new password is same as current
        if (password_verify($_POST['new_password'], $userData['password'])) {
            return ['success' => false, 'errors' => ['New password must be different from current password']];
        }

        // Hash new password
        $newPasswordHash = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

        // Update password in database
        if ($this->user->updatePassword(getCurrentUserId(), $newPasswordHash)) {
            return ['success' => true, 'message' => 'Password changed successfully'];
        }

        return ['success' => false, 'errors' => ['Failed to change password']];
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $controller = new ProfileController();

    switch ($_POST['action']) {
        case 'update_profile':
            echo json_encode($controller->updateProfile());
            break;
        case 'get_profile':
            echo json_encode($controller->getProfile());
            break;
        case 'change_password':
            echo json_encode($controller->changePassword());
            break;
    }
    exit();
}
