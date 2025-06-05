<?php
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/PasswordResetRepository.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class SessionService {
    public static function login(string $username, string $password): string {
        $user = UserRepository::findByUsername($username);
        if (!$user) return "Usuario no encontrado";

        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['language'] = $user['language'] ?? 'es';
            return "success";
        }
        return "Contraseña incorrecta";
    }

    public static function signup(string $username, string $email, string $password, string $gender): string {
        if (UserRepository::countByUsernameOrEmail($username, $email) > 0)
            return "El nombre de usuario o correo electrónico ya existe.";

        $profileImage = '/images/default-profile.png';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        if (UserRepository::insertUser($username, $email, $hashedPassword, $gender, $profileImage)) {
            return "Usuario registrado exitosamente";
        }
        return "Error al registrar el usuario";
    }

    public static function getUserData(int $userId): array {
        return UserRepository::getById($userId) ?? [];
    }

    public static function sendResetPasswordEmail(string $email): string {
        $user = UserRepository::findByEmail($email);
        if (!$user) return "No se encontró un usuario con ese correo electrónico";

        PasswordResetRepository::deleteByUserId($user['id']);
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        PasswordResetRepository::create($user['id'], $token, $expiresAt);

        $host = $_SERVER['SERVER_NAME'];
        $baseHost = str_ends_with($host, '.local') ? $host : gethostbyname($host);
        $resetLink = "https://$baseHost/reset_form?token=$token";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ibermejo@elpuig.xeill.net';
            $mail->Password = 'ghxg xloi senq hotj';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom('ibermejo@elpuig.xeill.net', 'ShopList');
            $mail->addAddress($email, $user['username']);
            $mail->isHTML(true);
            $mail->Subject = 'Restablecer contraseña - ShopList';
            $mail->Body = "
                <h2>Restablecimiento de contraseña</h2>
                <p>Hola {$user['username']},</p>
                <p>Haz clic en el siguiente enlace para restablecer tu contraseña:</p>
                <a href='$resetLink'>Restablecer contraseña</a>
                <p>Este enlace expirará en 1 hora.</p>
            ";
            $mail->send();
            return "Se ha enviado un correo con instrucciones para restablecer tu contraseña";
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $e->getMessage());
            return "Error al enviar el correo";
        }
    }

    public static function resetPasswordWithToken(string $token, string $newPassword): string {
        $tokenData = PasswordResetRepository::findByToken($token);
        if (!$tokenData) return "Token inválido o expirado";

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        if (!UserRepository::updatePassword($tokenData['user_id'], $hashedPassword)) {
            return "Error actualizando la contraseña";
        }

        PasswordResetRepository::deleteByToken($token);
        return "Contraseña actualizada correctamente.";
    }
}
