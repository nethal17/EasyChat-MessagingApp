<?php

require_once __DIR__ . '/../config/database.php';

class Message {
    private $conn;
    private $table = "messages";

    public $id;
    public $sender_id;
    public $receiver_id;
    public $message_text;
    public $image_path;
    public $is_read;
    public $deleted_at;
    public $deleted_by;

    // Constructor
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Send a new message
    public function send() {
        $query = "INSERT INTO " . $this->table . " 
                  (sender_id, receiver_id, message_text, image_path) 
                  VALUES (:sender_id, :receiver_id, :message_text, :image_path)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":sender_id", $this->sender_id);
        $stmt->bindParam(":receiver_id", $this->receiver_id);
        $stmt->bindParam(":message_text", $this->message_text);
        $stmt->bindParam(":image_path", $this->image_path);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Get conversation between two users
    public function getConversation($user1, $user2, $limit = 50) {
        $query = "SELECT m.*, 
                         sender.name as sender_name, 
                         sender.profile_picture as sender_picture,
                         receiver.name as receiver_name,
                         receiver.profile_picture as receiver_picture
                  FROM " . $this->table . " m
                  LEFT JOIN users sender ON m.sender_id = sender.id
                  LEFT JOIN users receiver ON m.receiver_id = receiver.id
                  WHERE (m.sender_id = :user1 AND m.receiver_id = :user2)
                     OR (m.sender_id = :user2 AND m.receiver_id = :user1)
                  ORDER BY m.created_at ASC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user1", $user1, PDO::PARAM_INT);
        $stmt->bindParam(":user2", $user2, PDO::PARAM_INT);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Get conversations list with last message and unread count
    public function getConversationsList($userId) {
        $query = "SELECT 
                    CASE 
                        WHEN m.sender_id = :user_id THEN m.receiver_id
                        ELSE m.sender_id
                    END as other_user_id,
                    u.name as other_user_name,
                    u.profile_picture as other_user_picture,
                    m.message_text as last_message,
                    m.image_path as last_message_image,
                    m.created_at as last_message_time,
                    m.sender_id as last_sender_id,
                    (SELECT COUNT(*) 
                     FROM messages 
                     WHERE sender_id = CASE 
                            WHEN m.sender_id = :user_id THEN m.receiver_id
                            ELSE m.sender_id
                          END
                     AND receiver_id = :user_id 
                     AND is_read = 0) as unread_count
                  FROM messages m
                  INNER JOIN (
                      SELECT 
                          CASE 
                              WHEN sender_id = :user_id THEN receiver_id
                              ELSE sender_id
                          END as other_id,
                          MAX(created_at) as max_time
                      FROM messages
                      WHERE sender_id = :user_id OR receiver_id = :user_id
                      GROUP BY other_id
                  ) latest ON (
                      (m.sender_id = :user_id AND m.receiver_id = latest.other_id)
                      OR (m.sender_id = latest.other_id AND m.receiver_id = :user_id)
                  ) AND m.created_at = latest.max_time
                  LEFT JOIN users u ON u.id = CASE 
                      WHEN m.sender_id = :user_id THEN m.receiver_id
                      ELSE m.sender_id
                  END
                  ORDER BY m.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Mark messages as read
    public function markAsRead($senderId, $receiverId) {
        $query = "UPDATE " . $this->table . " 
                  SET is_read = 1 
                  WHERE sender_id = :sender_id 
                  AND receiver_id = :receiver_id 
                  AND is_read = 0";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":sender_id", $senderId);
        $stmt->bindParam(":receiver_id", $receiverId);

        return $stmt->execute();
    }

    // Get unread message count for a user
    public function getUnreadCount($userId) {
        $query = "SELECT COUNT(*) as count 
                  FROM " . $this->table . " 
                  WHERE receiver_id = :user_id 
                  AND is_read = 0";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    // Get new messages since a specific time
    public function getNewMessages($user1, $user2, $sinceTime) {
        $query = "SELECT m.*, 
                         sender.name as sender_name, 
                         sender.profile_picture as sender_picture
                  FROM " . $this->table . " m
                  LEFT JOIN users sender ON m.sender_id = sender.id
                  WHERE ((m.sender_id = :user1 AND m.receiver_id = :user2)
                     OR (m.sender_id = :user2 AND m.receiver_id = :user1))
                  AND m.created_at > :since_time
                  ORDER BY m.created_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user1", $user1, PDO::PARAM_INT);
        $stmt->bindParam(":user2", $user2, PDO::PARAM_INT);
        $stmt->bindParam(":since_time", $sinceTime);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Soft delete a message (only sender can delete)
    public function deleteMessage($messageId, $userId) {
        // First verify the user is the sender
        $query = "SELECT sender_id FROM " . $this->table . " WHERE id = :message_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":message_id", $messageId, PDO::PARAM_INT);
        $stmt->execute();
        
        $message = $stmt->fetch();
        
        if (!$message || $message['sender_id'] != $userId) {
            return false; // User is not the sender
        }

        // Soft delete the message
        $query = "UPDATE " . $this->table . " 
                  SET deleted_at = NOW(), deleted_by = :deleted_by 
                  WHERE id = :message_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":deleted_by", $userId, PDO::PARAM_INT);
        $stmt->bindParam(":message_id", $messageId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Get recently deleted messages (for real-time notification)
    public function getRecentlyDeletedMessages($user1, $user2, $sinceTime) {
        $query = "SELECT id, deleted_at, deleted_by
                  FROM " . $this->table . "
                  WHERE ((sender_id = :user1 AND receiver_id = :user2)
                     OR (sender_id = :user2 AND receiver_id = :user1))
                  AND deleted_at IS NOT NULL
                  AND deleted_at > :since_time
                  ORDER BY deleted_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user1", $user1, PDO::PARAM_INT);
        $stmt->bindParam(":user2", $user2, PDO::PARAM_INT);
        $stmt->bindParam(":since_time", $sinceTime);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
