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
        elseif (isset($_POST['comment_content'])) {
            $this->handleNewComment();
        }
        elseif (isset($_POST['delete_comment'])) {
            DatabaseController::deleteComment($_SESSION['user_id'], $_POST['delete_comment']);
        }
        header("Location: /home");
        exit();
    }

    private function handleNewMessage() {
        $content = trim($_POST['content'] ?? '');
        $imagePath = null;
        $listaId = $_POST['lista_id'] ?? null;
        $isPublic = isset($_POST['lista_publica']) ? 1 : 0;
        
        // Validación básica
        if (empty($content) && empty($_FILES['message_image']['name']) && empty($listaId)) {
            $_SESSION['error'] = "Debes escribir un mensaje, subir una imagen o compartir una lista";
            return;
        }

        try {
            $this->db->beginTransaction();
            
            // Insertar mensaje (aunque no tenga contenido)
            $stmt = $this->db->prepare(
                "INSERT INTO messages (user_id, content, image_path, created_at) VALUES (?, ?, ?, NOW())"
            );
            $stmt->execute([$_SESSION['user_id'], $content, $imagePath]);
            $messageId = $this->db->lastInsertId();
            
            // Si se está compartiendo una lista
            if ($listaId) {
                // Verificar que la lista pertenece al usuario
                $stmt = $this->db->prepare("SELECT id FROM listas WHERE id = ? AND usuario_id = ?");
                $stmt->execute([$listaId, $_SESSION['user_id']]);
                
                if (!$stmt->fetch()) {
                    throw new Exception("No tienes permiso para compartir esta lista");
                }
                
                // Compartir la lista
                $stmt = $this->db->prepare(
                    "INSERT INTO shared_lists (message_id, lista_id, shared_by, is_public) VALUES (?, ?, ?, ?)"
                );
                if (!$stmt->execute([$messageId, $listaId, $_SESSION['user_id'], $isPublic])) {
                    throw new Exception("Error al compartir la lista");
                }
            }
            
            // Procesar imagen si existe
            if (!empty($_FILES['message_image']['name'])) {
                $imagePath = $this->handleImageUpload();
                if ($imagePath) {
                    $stmt = $this->db->prepare(
                        "UPDATE messages SET image_path = ? WHERE id = ?"
                    );
                    $stmt->execute([$imagePath, $messageId]);
                }
            }
            
            $this->db->commit();
            $_SESSION['success'] = "Publicación creada correctamente";
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Error al publicar: " . $e->getMessage();
        }
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

    // private function getAllMessagesWithUsers() {
    //     $stmt = $this->db->query("
    //         SELECT m.*, 
    //                u.username, 
    //                u.profile_image,
    //                (SELECT COUNT(*) FROM likes WHERE message_id = m.id) as like_count,
    //                EXISTS(SELECT 1 FROM likes WHERE message_id = m.id AND user_id = {$_SESSION['user_id']}) as has_liked
    //         FROM messages m
    //         JOIN User u ON m.user_id = u.id
    //         ORDER BY m.created_at DESC
    //         LIMIT 50
    //     ");
    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }


    private function handleNewComment() {
        $content = trim($_POST['comment_content'] ?? '');
        $messageId = $_POST['message_id'] ?? 0;
        
        if (!empty($content) && $messageId) {
            DatabaseController::addComment($_SESSION['user_id'], $messageId, $content);
        }
    }

    private function getAllMessagesWithUsers() {
        $stmt = $this->db->query("
            SELECT m.*, 
                u.username, 
                u.profile_image,
                (SELECT COUNT(*) FROM likes WHERE message_id = m.id) as like_count,
                EXISTS(SELECT 1 FROM likes WHERE message_id = m.id AND user_id = {$_SESSION['user_id']}) as has_liked,
                (SELECT COUNT(*) FROM comments WHERE message_id = m.id) as comment_count,
                EXISTS(SELECT 1 FROM shared_lists WHERE message_id = m.id) as has_shared_list
            FROM messages m
            JOIN User u ON m.user_id = u.id
            ORDER BY m.created_at DESC
            LIMIT 50
        ");
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener comentarios para cada mensaje
        foreach ($messages as &$message) {
            $message['comments'] = DatabaseController::getComments($message['id']);
        }
        
        return $messages;
    }
}