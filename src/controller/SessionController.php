<?php
require_once __DIR__ . '/../services/SessionService.php';

class SessionController {
    public static function login($username, $password): string {
        return SessionService::login($username, $password);
    }

    public static function signup($username, $email, $password, $gender): string {
        return SessionService::signup($username, $email, $password, $gender);
    }

    public static function getUserData($userId): array {
        return SessionService::getUserData($userId);
    }

    public static function sendResetPasswordEmail($email): string {
        return SessionService::sendResetPasswordEmail($email);
    }

    public static function resetPasswordWithToken($token, $newPassword): string {
        return SessionService::resetPasswordWithToken($token, $newPassword);
    }
}
