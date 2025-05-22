<?php
require_once 'DatabaseController.php';
require_once __DIR__ . '/../../vendor/autoload.php';  // Ajusta la ruta según tu estructura
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class SessionController
{
    public static function userLogin(string $username, string $password): string
    {
        $db = DatabaseController::connect();

        $query = "SELECT * FROM User WHERE username = :username LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verificar contraseña con password_verify
            if (password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['language'] = $user['language'] ?? 'es';

                return "success";
            } else {
                return "Contraseña incorrecta";
            }
        } else {
            return "Usuario no encontrado";
        }
    }

    public static function userSignUp(string $username, string $email, string $password, string $gender): string
    {
        $db = DatabaseController::connect();

        // Validar usuario y email
        $queryCheck = "SELECT COUNT(*) FROM User WHERE username = :username OR email = :email";
        $stmtCheck = $db->prepare($queryCheck);
        $stmtCheck->bindParam(':username', $username);
        $stmtCheck->bindParam(':email', $email);
        $stmtCheck->execute();
        $count = $stmtCheck->fetchColumn();

        if ($count > 0) {
            return "El nombre de usuario o correo electrónico ya existe.";
        }

        // Insertar usuario
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $queryInsert = "INSERT INTO User (username, email, password, gender, language) VALUES (:username, :email, :password, :gender, 'es')";
        $stmtInsert = $db->prepare($queryInsert);
        $stmtInsert->bindParam(':username', $username);
        $stmtInsert->bindParam(':email', $email);
        $stmtInsert->bindParam(':password', $hashedPassword);
        $stmtInsert->bindParam(':gender', $gender);

        if ($stmtInsert->execute()) {
            return "Usuario registrado exitosamente";
        } else {
            return "Error al registrar el usuario";
        }
    }

    public static function getUserData(int $userId): array
    {
        $db = DatabaseController::connect();

        $query = "SELECT id, username, email, gender FROM User WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: [];
    }

    // Función para enviar correo de recuperación de contraseña con token
    public static function sendResetPasswordEmail(string $email): string
    {
        $db = DatabaseController::connect();

        // Buscar usuario
        $query = "SELECT id, username FROM User WHERE email = :email LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return "No se encontró un usuario con ese correo electrónico";
        }

        // Eliminar tokens antiguos para este usuario
        $queryDelete = "DELETE FROM password_resets WHERE user_id = :user_id";
        $stmtDelete = $db->prepare($queryDelete);
        $stmtDelete->bindParam(':user_id', $user['id']);
        $stmtDelete->execute();

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Guardar token
        $queryInsert = "INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)";
        $stmtInsert = $db->prepare($queryInsert);
        $stmtInsert->bindParam(':user_id', $user['id']);
        $stmtInsert->bindParam(':token', $token);
        $stmtInsert->bindParam(':expires_at', $expiresAt);
        $stmtInsert->execute();

        // Usar la URL base dinámica
        $protocol = "https://";
        $host = "www.sintesi.local"; // Asegúrate de que sea el mismo que usa tu app
        $resetLink = "{$protocol}{$host}/reset_form?token={$token}";
error_log("Enviando enlace de recuperación: " . $resetLink);
        // Enviar correo con PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Config SMTP
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
                <p>Hemos recibido una solicitud para restablecer tu contraseña. Haz clic en el siguiente enlace para continuar:</p>
                <p><a href='$resetLink' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-align: center; text-decoration: none; display: inline-block; border-radius: 5px;'>Restablecer contraseña</a></p>
                <p>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
                <p>El enlace expirará en 1 hora.</p>
                <hr>
                <p><small>Equipo de ShopList</small></p>
            ";

            $mail->send();
            return "Se ha enviado un correo con instrucciones para restablecer tu contraseña";
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $e->getMessage());
            return "Error al enviar el correo. Por favor, intenta nuevamente más tarde.";
        }
    }

    // Función para restablecer la contraseña usando el token
    public static function resetPasswordWithToken(string $token, string $newPassword): string
{
    $db = DatabaseController::connect();
    
    // 1. Get token info with additional debug logging
    error_log("Searching for token: " . $token);
    $stmt = $db->prepare("SELECT pr.user_id, pr.expires_at, u.username 
                         FROM password_resets pr
                         JOIN User u ON pr.user_id = u.id
                         WHERE pr.token = ?");
    
    if (!$stmt->execute([$token])) {
        $error = $stmt->errorInfo();
        error_log("Token query error: " . print_r($error, true));
        return "Error verifying token";
    }
    
    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tokenData) {
        error_log("Token not found: " . $token);
        return "Invalid or expired token";
    }
    
    // 2. Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    if (!$hashedPassword) {
        return "Error processing password";
    }

    // 3. Update password in User table
    $updateStmt = $db->prepare("UPDATE User SET password = ? WHERE id = ?");
    if (!$updateStmt->execute([$hashedPassword, $tokenData['user_id']])) {
        $error = $updateStmt->errorInfo();
        error_log("Password update error: " . print_r($error, true));
        return "Error updating password";
    }

    // 4. Delete the used token
    $db->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);
    
    return "Password reset successfully";
}
}
?>
