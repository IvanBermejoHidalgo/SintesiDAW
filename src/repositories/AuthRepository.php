<?php
namespace Repositories;

use PDO;
use DatabaseController;

class UserRepository {
    private PDO $db;

    public function __construct() {
        $this->db = DatabaseController::getConnection();
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM User WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function updateResetToken(string $email, string $token): void {
        $stmt = $this->db->prepare("UPDATE User SET reset_token = ? WHERE email = ?");
        $stmt->execute([$token, $email]);
    }

    public function updatePasswordByToken(string $token, string $hashedPassword): void {
        $stmt = $this->db->prepare("UPDATE User SET password = ?, reset_token = NULL WHERE reset_token = ?");
        $stmt->execute([$hashedPassword, $token]);
    }

    public function findPasswordResetToken(string $token): ?array {
        $stmt = $this->db->prepare("SELECT * FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public function updatePasswordByUserId(int $userId, string $hashedPassword): void {
        $stmt = $this->db->prepare("UPDATE User SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $userId]);
    }

    public function deleteResetToken(string $token): void {
        $stmt = $this->db->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
    }
}
