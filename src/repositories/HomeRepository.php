<?php
require_once __DIR__ . '/../controller/DatabaseController.php';

class HomeRepository {
    private PDO $db;

    public function __construct() {
        $this->db = DatabaseController::connect();
    }

    public function createMessageWithOptionalImageAndList(): int {
        $content = trim($_POST['content'] ?? '');
        $listaId = $_POST['lista_id'] ?? null;
        $isPublic = isset($_POST['lista_publica']) ? 1 : 0;
        $imagePath = null;

        if (empty($content) && empty($_FILES['message_image']['name']) && empty($listaId)) {
            $_SESSION['error'] = "Debes escribir un mensaje, subir una imagen o compartir una lista";
            return 0;
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("INSERT INTO messages (user_id, content, image_path, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$_SESSION['user_id'], $content, $imagePath]);
            $messageId = $this->db->lastInsertId();

            if ($listaId) {
                $stmt = $this->db->prepare("SELECT id FROM listas WHERE id = ? AND usuario_id = ?");
                $stmt->execute([$listaId, $_SESSION['user_id']]);
                if (!$stmt->fetch()) throw new Exception("No tienes permiso para compartir esta lista");

                $stmt = $this->db->prepare("INSERT INTO shared_lists (message_id, lista_id, shared_by, is_public) VALUES (?, ?, ?, ?)");
                $stmt->execute([$messageId, $listaId, $_SESSION['user_id'], $isPublic]);
            }

            if (!empty($_FILES['message_image']['name'])) {
                $imagePath = $this->uploadImage();
                if ($imagePath) {
                    $stmt = $this->db->prepare("UPDATE messages SET image_path = ? WHERE id = ?");
                    $stmt->execute([$imagePath, $messageId]);
                }
            }

            $this->db->commit();
             $_SESSION['success'] = "Publicación creada correctamente";
            return (int)$messageId; // <<< ESTE RETURN
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Error al publicar: " . $e->getMessage();
            return 0; // Por si acaso
        }
    }

    public function uploadImage(): ?string {
        if (!isset($_FILES['message_image']) || $_FILES['message_image']['error'] !== UPLOAD_ERR_OK) return null;

        $uploadDir = __DIR__ . '/../../public/uploads/messages/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['message_image']['type'], $allowedTypes)) return null;

        if ($_FILES['message_image']['size'] > 5_000_000) return null;

        $extension = pathinfo($_FILES['message_image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $path = $uploadDir . $filename;

        return move_uploaded_file($_FILES['message_image']['tmp_name'], $path)
            ? '/uploads/messages/' . $filename
            : null;
    }

    public static function deleteMessage($userId, $messageId) {
        $pdo = DatabaseController::connect();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("DELETE FROM likes WHERE message_id = ?");
            $stmt->execute([$messageId]);
            $stmt = $pdo->prepare("DELETE FROM comments WHERE message_id = ?");
            $stmt->execute([$messageId]);
            $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ? AND user_id = ?");
            $stmt->execute([$messageId, $userId]);
            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error deleting message: " . $e->getMessage());
            return false;
        }
    }

    public static function addLike($userId, $messageId) {
      $pdo = DatabaseController::connect();
      try {
          $stmt = $pdo->prepare("INSERT INTO likes (user_id, message_id) VALUES (?, ?)");
          return $stmt->execute([$userId, $messageId]);
      } catch (PDOException $e) {
          // Evita errores si ya dio like
          return false;
      }
    }

    public static function removeLike($userId, $messageId) {
        $pdo = DatabaseController::connect();
        $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND message_id = ?");
        return $stmt->execute([$userId, $messageId]);
    }

    public static function addComment($userId, $messageId, $content) {
        $pdo = DatabaseController::connect();
        $stmt = $pdo->prepare("INSERT INTO comments (user_id, message_id, content) VALUES (?, ?, ?)");
        $success = $stmt->execute([$userId, $messageId, $content]);
        
        if ($success) {
            $commentId = $pdo->lastInsertId();
            $stmt = $pdo->prepare("
                SELECT c.*, u.username, u.profile_image, 
                    COALESCE(u.profile_image, '/images/default-profile.png') as profile_image
                FROM comments c
                JOIN User u ON c.user_id = u.id
                WHERE c.id = ?
            ");
            $stmt->execute([$commentId]);
            $comment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Asegurar que siempre haya una imagen de perfil
            if (empty($comment['profile_image'])) {
                $comment['profile_image'] = '/images/default-profile.png';
            }
            
            return $comment;
        }
        
        return false;
    }

    public static function deleteComment($userId, $commentId) {
        $pdo = DatabaseController::connect();
        
        // Primero obtener el message_id para actualizar el contador
        $stmt = $pdo->prepare("SELECT message_id FROM comments WHERE id = ? AND user_id = ?");
        $stmt->execute([$commentId, $userId]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$comment) return false;
        
        // Eliminar el comentario
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
        $success = $stmt->execute([$commentId, $userId]);
        
        if ($success) {
            return [
                'success' => true,
                'message_id' => $comment['message_id']
            ];
        }
        
        return false;
    }

    public function getAllMessagesWithUsers(int $currentUserId): array {
        $stmt = $this->db->query("
            SELECT m.*, u.username, u.profile_image,
                (SELECT COUNT(*) FROM likes WHERE message_id = m.id) as like_count,
                EXISTS(SELECT 1 FROM likes WHERE message_id = m.id AND user_id = $currentUserId) as has_liked,
                (SELECT COUNT(*) FROM comments WHERE message_id = m.id) as comment_count,
                EXISTS(SELECT 1 FROM shared_lists WHERE message_id = m.id) as has_shared_list
            FROM messages m
            JOIN User u ON m.user_id = u.id
            ORDER BY m.created_at DESC
            LIMIT 50
        ");

        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($messages as &$msg) {
            $msg['comments'] = HomeRepository::getComments($msg['id']);
        }
        return $messages;
    }

    public static function getComments($messageId) {
        $pdo = DatabaseController::connect();
        $stmt = $pdo->prepare("
            SELECT c.*, u.username, u.profile_image 
            FROM comments c
            JOIN User u ON c.user_id = u.id
            WHERE c.message_id = ?
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([$messageId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById($userId) {
        $stmt = $this->db->prepare("
            SELECT id, username, email, password, profile_image, created_at 
            FROM User 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMessageById($messageId, $currentUserId): ?array {
        $stmt = $this->db->prepare("
            SELECT m.id, m.user_id, m.content, m.image_path, m.created_at,
                u.username, u.profile_image,
                (SELECT COUNT(*) FROM likes WHERE message_id = m.id) as like_count,
                EXISTS(SELECT 1 FROM likes WHERE message_id = m.id AND user_id = ?) as has_liked,
                (SELECT COUNT(*) FROM comments WHERE message_id = m.id) as comment_count,
                EXISTS(SELECT 1 FROM shared_lists WHERE message_id = m.id) as has_shared_list
            FROM messages m
            JOIN User u ON m.user_id = u.id
            WHERE m.id = ?
        ");
        $stmt->execute([$currentUserId, $messageId]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($message) {
            $message['comments'] = HomeRepository::getComments($message['id']);
        }

        return $message;
    }
}