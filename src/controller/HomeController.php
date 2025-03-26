<?php

class HomeController {
    private $db;

    public function __construct() {
        $this->db = DatabaseController::connect();
    }

    public function handleRequest() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /");
            exit();
        }

        $this->handlePostActions();
        
        return [
            'messages' => $this->getAllMessagesWithUsers(),
            'userData' => SessionController::getUserData($_SESSION['user_id'])
        ];
    }

    private function handlePostActions() {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") return;

        if (isset($_POST['content'])) {
            $content = htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8');
            $stmt = $this->db->prepare(
                "INSERT INTO messages (user_id, content, created_at) VALUES (?, ?, NOW())"
            );
            $stmt->execute([$_SESSION['user_id'], $content]);
        } 
        elseif (isset($_POST['delete_message'])) {
            DatabaseController::deleteMessage($_SESSION['user_id'], $_POST['delete_message']);
        }
        elseif (isset($_POST['like_message'])) {
            DatabaseController::addLike($_SESSION['user_id'], $_POST['like_message']);
        }
        elseif (isset($_POST['unlike_message'])) {
            DatabaseController::removeLike($_SESSION['user_id'], $_POST['unlike_message']);
        }

        header("Location: /home");
        exit();
    }

    private function getAllMessagesWithUsers() {
        $stmt = $this->db->query("
            SELECT m.*, u.username, u.profile_image 
            FROM messages m
            JOIN User u ON m.user_id = u.id
            ORDER BY m.created_at DESC
            LIMIT 50
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}