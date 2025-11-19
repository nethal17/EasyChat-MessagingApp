<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/User.php';

class MessageController {
    private $message;
    private $user;

    public function __construct() {
        $this->message = new Message();
        $this->user = new User();
    }

    // Send a message
    public function sendMessage() {
        if (!isLoggedIn()) {
            return ['success' => false, 'errors' => ['Not authenticated']];
        }

        $errors = [];

        if (empty($_POST['receiver_id'])) {
            $errors[] = "Receiver is required";
        }

        if (empty($_POST['message_text']) && empty($_FILES['message_image'])) {
            $errors[] = "Message text or image is required";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Set message data
        $this->message->sender_id = getCurrentUserId();
        $this->message->receiver_id = intval($_POST['receiver_id']);
        $this->message->message_text = cleanInput($_POST['message_text'] ?? '');
        $this->message->image_path = null;

        // Handle image upload
        if (isset($_FILES['message_image']) && $_FILES['message_image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleMessageImageUpload($_FILES['message_image']);
            
            if ($uploadResult['success']) {
                $this->message->image_path = $uploadResult['filename'];
            } else {
                return $uploadResult;
            }
        }

        // Send message
        $messageId = $this->message->send();

        if ($messageId) {
            return [
                'success' => true, 
                'message' => 'Message sent',
                'message_id' => $messageId
            ];
        }

        return ['success' => false, 'errors' => ['Failed to send message']];
    }

    // Get conversation between current user and another user
    public function getConversation() {
        if (!isLoggedIn()) {
            return ['success' => false, 'errors' => ['Not authenticated']];
        }

        if (empty($_GET['user_id'])) {
            return ['success' => false, 'errors' => ['User ID is required']];
        }

        $currentUserId = getCurrentUserId();
        $otherUserId = intval($_GET['user_id']);

        // Get messages
        $messages = $this->message->getConversation($currentUserId, $otherUserId);

        // Mark messages as read
        $this->message->markAsRead($otherUserId, $currentUserId);

        return [
            'success' => true,
            'messages' => $messages
        ];
    }

    // Get conversations list
    public function getConversationsList() {
        if (!isLoggedIn()) {
            return ['success' => false, 'errors' => ['Not authenticated']];
        }

        $conversations = $this->message->getConversationsList(getCurrentUserId());

        return [
            'success' => true,
            'conversations' => $conversations
        ];
    }

    // Get new messages (for polling)
    public function getNewMessages() {
        if (!isLoggedIn()) {
            return ['success' => false, 'errors' => ['Not authenticated']];
        }

        if (empty($_GET['user_id']) || empty($_GET['since'])) {
            return ['success' => false, 'errors' => ['User ID and timestamp are required']];
        }

        $currentUserId = getCurrentUserId();
        $otherUserId = intval($_GET['user_id']);
        $sinceTime = $_GET['since'];

        $messages = $this->message->getNewMessages($currentUserId, $otherUserId, $sinceTime);

        // Mark new messages as read
        if (!empty($messages)) {
            $this->message->markAsRead($otherUserId, $currentUserId);
        }

        return [
            'success' => true,
            'messages' => $messages
        ];
    }

    // Get all users for starting a conversation
    public function getAllUsers() {
        if (!isLoggedIn()) {
            return ['success' => false, 'errors' => ['Not authenticated']];
        }

        $users = $this->user->getAllUsersExcept(getCurrentUserId());

        return [
            'success' => true,
            'users' => $users
        ];
    }

    // Get unread message count
    public function getUnreadCount() {
        if (!isLoggedIn()) {
            return ['success' => false, 'errors' => ['Not authenticated']];
        }

        $count = $this->message->getUnreadCount(getCurrentUserId());

        return [
            'success' => true,
            'count' => $count
        ];
    }

    // Delete a message (only sender can delete)
    public function deleteMessage() {
        if (!isLoggedIn()) {
            return ['success' => false, 'errors' => ['Not authenticated']];
        }

        if (empty($_POST['message_id'])) {
            return ['success' => false, 'errors' => ['Message ID is required']];
        }

        $messageId = intval($_POST['message_id']);
        $userId = getCurrentUserId();

        $result = $this->message->deleteMessage($messageId, $userId);

        if ($result) {
            return [
                'success' => true,
                'message' => 'Message deleted',
                'message_id' => $messageId
            ];
        }

        return ['success' => false, 'errors' => ['Failed to delete message or you are not the sender']];
    }

    // Get recently deleted messages (for polling)
    public function getDeletedMessages() {
        if (!isLoggedIn()) {
            return ['success' => false, 'errors' => ['Not authenticated']];
        }

        if (empty($_GET['user_id']) || empty($_GET['since'])) {
            return ['success' => false, 'errors' => ['User ID and timestamp are required']];
        }

        $currentUserId = getCurrentUserId();
        $otherUserId = intval($_GET['user_id']);
        $sinceTime = $_GET['since'];

        $deletedMessages = $this->message->getRecentlyDeletedMessages($currentUserId, $otherUserId, $sinceTime);

        return [
            'success' => true,
            'deleted_messages' => $deletedMessages
        ];
    }

    // Handle message image upload
    private function handleMessageImageUpload($file) {
        // Validate file type
        $fileType = mime_content_type($file['tmp_name']);
        if (!in_array($fileType, ALLOWED_IMAGE_TYPES)) {
            return ['success' => false, 'errors' => ['Invalid file type']];
        }

        // Validate file size
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['success' => false, 'errors' => ['File too large']];
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'msg_' . getCurrentUserId() . '_' . time() . '_' . uniqid() . '.' . $extension;
        $uploadPath = MESSAGE_UPLOAD_PATH . $filename;

        // Create directory if it doesn't exist
        if (!is_dir(MESSAGE_UPLOAD_PATH)) {
            mkdir(MESSAGE_UPLOAD_PATH, 0755, true);
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return ['success' => true, 'filename' => $filename];
        }

        return ['success' => false, 'errors' => ['Failed to upload file']];
    }
}

// Handle AJAX requests
if (isset($_GET['action']) || isset($_POST['action'])) {
    header('Content-Type: application/json');
    $controller = new MessageController();

    $action = $_GET['action'] ?? $_POST['action'];

    switch ($action) {
        case 'send':
            echo json_encode($controller->sendMessage());
            break;
        case 'get_conversation':
            echo json_encode($controller->getConversation());
            break;
        case 'get_conversations_list':
            echo json_encode($controller->getConversationsList());
            break;
        case 'get_new_messages':
            echo json_encode($controller->getNewMessages());
            break;
        case 'get_users':
            echo json_encode($controller->getAllUsers());
            break;
        case 'get_unread_count':
            echo json_encode($controller->getUnreadCount());
            break;
        case 'delete_message':
            echo json_encode($controller->deleteMessage());
            break;
        case 'get_deleted_messages':
            echo json_encode($controller->getDeletedMessages());
            break;
    }
    exit();
}
