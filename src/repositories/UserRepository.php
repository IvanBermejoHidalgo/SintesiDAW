<?php
require_once __DIR__ . '/../controller/DatabaseController.php';

class UserRepository {
    public static function findByUsername(string $username): ?array {
        $db = DatabaseController::connect();
        $stmt = $db->prepare("SELECT * FROM User WHERE username = :username LIMIT 1");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function findByEmail(string $email): ?array {
        $db = DatabaseController::connect();
        $stmt = $db->prepare("SELECT * FROM User WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function countByUsernameOrEmail(string $username, string $email): int {
        $db = DatabaseController::connect();
        $stmt = $db->prepare("SELECT COUNT(*) FROM User WHERE username = :username OR email = :email");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public static function insertUser(string $username, string $email, string $hashedPassword, string $gender, string $profileImage): bool {
        $db = DatabaseController::connect();
        $stmt = $db->prepare("INSERT INTO User (username, email, password, gender, profile_image) VALUES (:username, :email, :password, :gender, :profile_image)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':profile_image', $profileImage);
        return $stmt->execute();
    }

    public static function updatePassword(int $userId, string $hashedPassword): bool {
        $db = DatabaseController::connect();
        $stmt = $db->prepare("UPDATE User SET password = :password WHERE id = :id");
        return $stmt->execute([':password' => $hashedPassword, ':id' => $userId]);
    }

    public static function getById(int $id): ?array {
        $db = DatabaseController::connect();
        $stmt = $db->prepare("SELECT id, username, email, gender FROM User WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
