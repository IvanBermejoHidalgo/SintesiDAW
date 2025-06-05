<?php
require_once __DIR__ . '/../controller/DatabaseController.php';

class PasswordResetRepository {
    public static function deleteByUserId(int $userId): void {
        $db = DatabaseController::connect();
        $stmt = $db->prepare("DELETE FROM password_resets WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
    }

    public static function create(int $userId, string $token, string $expiresAt): bool {
        $db = DatabaseController::connect();
        $stmt = $db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
        return $stmt->execute([
            ':user_id' => $userId,
            ':token' => $token,
            ':expires_at' => $expiresAt
        ]);
    }

    public static function findByToken(string $token): ?array {
        $db = DatabaseController::connect();
        $stmt = $db->prepare("SELECT pr.user_id, pr.expires_at, u.username 
                              FROM password_resets pr
                              JOIN User u ON pr.user_id = u.id
                              WHERE pr.token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function deleteByToken(string $token): void {
        $db = DatabaseController::connect();
        $stmt = $db->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
    }
}
