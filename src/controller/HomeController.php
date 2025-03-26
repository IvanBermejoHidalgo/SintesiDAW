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
            'userData' => SessionController::getUserData($_SESSION['user_id']),
            'current_user_id' => $_SESSION['user_id'] // Añade esta línea
        ];
    }

    private function handlePostActions() {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") return;

        if (isset($_POST['content'])) {
            $this->handleNewMessage();
        } 
        elseif (isset($_POST['delete_message'])) {
            $this->handleDeleteMessage();
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

    private function handleNewMessage() {
        $content = trim($_POST['content'] ?? '');
        $hasImage = isset($_FILES['message_image']) && $_FILES['message_image']['error'] === UPLOAD_ERR_OK;
        
        if (empty($content) && !$hasImage) {
            $_SESSION['error'] = "Debes escribir un mensaje o subir una imagen";
            return;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO messages (user_id, content, image_path, created_at) VALUES (?, ?, ?, NOW())"
        );
        $stmt->execute([$_SESSION['user_id'], $content, $imagePath]);
    }

    private function handleDeleteMessage() {
        // Obtener la imagen asociada al mensaje
        $stmt = $this->db->prepare("SELECT image_path FROM messages WHERE id = ? AND user_id = ?");
        $stmt->execute([$_POST['delete_message'], $_SESSION['user_id']]);
        $message = $stmt->fetch();
    
        // Eliminar el mensaje
        DatabaseController::deleteMessage($_SESSION['user_id'], $_POST['delete_message']);
    
        // Si existe imagen asociada, borrarla del servidor
        if ($message && $message['image_path']) {
            $filePath = __DIR__ . '/../../public' . $message['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    private function handleImageUpload() {
        if (!isset($_FILES['message_image']) || $_FILES['message_image']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Configuración de la subida
        $uploadDir = __DIR__ . '/../../public/uploads/messages/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Validar tipo de archivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['message_image']['type'];
        if (!in_array($fileType, $allowedTypes)) {
            return null;
        }

        // Validar tamaño (max 5MB)
        if ($_FILES['message_image']['size'] > 5000000) {
            return null;
        }

        // Generar nombre único
        $extension = pathinfo($_FILES['message_image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $filename;

        // Mover el archivo
        if (move_uploaded_file($_FILES['message_image']['tmp_name'], $targetPath)) {
            return '/uploads/messages/' . $filename;
        }

        return null;
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