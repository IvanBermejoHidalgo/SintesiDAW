<?php
namespace services;

use repositories\UserRepository;
use SessionController;

class AuthService {
    private UserRepository $repo;

    public function __construct() {
        $this->repo = new UserRepository();
    }

    public function requestPasswordReset(string $email): void {
        $user = $this->repo->findByEmail($email);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $this->repo->updateResetToken($email, $token);
            SessionController::sendResetEmail($email, $token);
        }
    }

    public function resetPassword(string $token, string $password): void {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $this->repo->updatePasswordByToken($token, $hashed);
    }

    public function changePassword(string $password, string $confirmPassword, string $token): string {
        if ($password !== $confirmPassword) {
            return "Las contraseñas no coinciden.";
        }

        $resetData = $this->repo->findPasswordResetToken($token);
        if (!$resetData) {
            return "Token no válido o expirado.";
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $this->repo->updatePasswordByUserId($resetData['user_id'], $hashed);
        $this->repo->deleteResetToken($token);
        return "success";
    }
}
