<?php
require_once __DIR__ . '/../controller/DatabaseController.php';

class ProfileRepository {

    public static function getUserById($userId) {
        $pdo = DatabaseController::connect();
        $stmt = $pdo->prepare("
            SELECT id, username, email, password, profile_image, created_at 
            FROM User 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function updateUsername($userId, $newUsername) {
        $pdo = DatabaseController::connect();
        $stmt = $pdo->prepare("UPDATE User SET username = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$newUsername, $userId]);
    }

    public static function updateProfileImage($userId, $imagePath) {
        $pdo = DatabaseController::connect();
        $stmt = $pdo->prepare("
            UPDATE User 
            SET profile_image = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$imagePath, $userId]);
    }

    public static function deleteUserAccount($userId) {
        $pdo = DatabaseController::connect();
        $pdo->beginTransaction();
        error_log("Iniciando eliminación de cuenta para usuario ID: $userId");
        try {
            error_log("Eliminando likes...");
            $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ?");
            $stmt->execute([$userId]);
            error_log("Eliminando comentarios...");
            $stmt = $pdo->prepare("DELETE FROM comments WHERE user_id = ?");
            $stmt->execute([$userId]);
            $stmt = $pdo->prepare("DELETE FROM messages WHERE user_id = ?");
            $stmt->execute([$userId]);
            $stmt = $pdo->prepare("DELETE FROM carrito WHERE usuario_id = ?");
            $stmt->execute([$userId]);
            $stmt = $pdo->prepare("SELECT id FROM listas WHERE usuario_id = ?");
            $stmt->execute([$userId]);
            $listas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($listas)) {
                $placeholders = implode(',', array_fill(0, count($listas), '?'));
                $stmt = $pdo->prepare("DELETE FROM lista_productos WHERE lista_id IN ($placeholders)");
                $stmt->execute($listas);
                $stmt = $pdo->prepare("DELETE FROM listas WHERE usuario_id = ?");
                $stmt->execute([$userId]);
            }
            $stmt = $pdo->prepare("SELECT id FROM pedidos WHERE usuario_id = ?");
            $stmt->execute([$userId]);
            $pedidos = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($pedidos)) {
                $placeholders = implode(',', array_fill(0, count($pedidos), '?'));
                $stmt = $pdo->prepare("DELETE FROM pedido_detalles WHERE pedido_id IN ($placeholders)");
                $stmt->execute($pedidos);
                $stmt = $pdo->prepare("DELETE FROM pedidos WHERE usuario_id = ?");
                $stmt->execute([$userId]);
            }
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $stmt->execute([$userId]);
            error_log("Eliminando usuario...");
            $stmt = $pdo->prepare("DELETE FROM User WHERE id = ?");
            $stmt->execute([$userId]);
            $pdo->commit();
            error_log("Cuenta eliminada exitosamente");
            $user = self::getUserById($userId);
            if ($user && $user['profile_image'] && $user['profile_image'] !== '/images/default-profile.png') {
                $imagePath = $_SERVER['DOCUMENT_ROOT'] . $user['profile_image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            return true;
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Error al eliminar cuenta de usuario: " . $e->getMessage());
            return false;
        }
    }

    public static function getMessagesByUser($userId, $currentUserId = null) {
        $pdo = DatabaseController::connect();
        $query = "
            SELECT m.*, u.username, u.profile_image,
            (SELECT COUNT(*) FROM likes WHERE message_id = m.id) as like_count,
            (SELECT COUNT(*) FROM comments WHERE message_id = m.id) as comment_count,
            EXISTS(SELECT 1 FROM shared_lists WHERE message_id = m.id) as has_shared_list";

        if ($currentUserId !== null) {
            $query .= ",
            EXISTS(SELECT 1 FROM likes WHERE message_id = m.id AND user_id = :current_user) as has_liked";
        }
        
        $query .= "
            FROM messages m
            JOIN User u ON m.user_id = u.id
            WHERE m.user_id = :user_id
            ORDER BY m.created_at DESC";
        
        $stmt = $pdo->prepare($query);
        $params = [':user_id' => $userId];
        
        if ($currentUserId !== null) {
            $params[':current_user'] = $currentUserId;
        }
        
        $stmt->execute($params);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($messages as &$message) {
            $message['comments'] = self::getComments($message['id']);
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
}
