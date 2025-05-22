<?php
namespace Controller;

use DatabaseController;
use SessionController;

class AuthController {
    public static function showResetForm() {
        include_once '../vendor/autoload.php';
        $loader = new \Twig\Loader\FilesystemLoader('../public/views');
        $twig = new \Twig\Environment($loader);
        echo $twig->render('reset_request.twig');
    }

    public static function handleResetRequest() {
        $email = $_POST['email'];
        $pdo = DatabaseController::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM User WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $stmt = $pdo->prepare("UPDATE User SET reset_token = ? WHERE email = ?");
            $stmt->execute([$token, $email]);

            SessionController::sendResetEmail($email, $token);
        }

        header("Location: /reset-success");
    }

    public static function showResetPasswordForm() {
        $token = $_GET['token'] ?? '';
        include_once '../vendor/autoload.php';
        $loader = new \Twig\Loader\FilesystemLoader('../public/views');
        $twig = new \Twig\Environment($loader);
        echo $twig->render('reset_form.twig', ['token' => $token]);
    }

    public static function handlePasswordReset() {
        $token = $_POST['token'];
        $newPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $pdo = DatabaseController::getConnection();
        $stmt = $pdo->prepare("UPDATE User SET password = ?, reset_token = NULL WHERE reset_token = ?");
        $stmt->execute([$newPassword, $token]);

        header("Location: /login");
    }

    public static function showSuccess() {
        include_once '../vendor/autoload.php';
        $loader = new \Twig\Loader\FilesystemLoader('../public/views');
        $twig = new \Twig\Environment($loader);
        echo $twig->render('reset_success.twig');
    }

    public function handleChangePassword()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $token = $_POST['token'] ?? '';

        if ($password !== $confirmPassword) {
            echo "Las contraseñas no coinciden.";
            return;
        }

        // Verificamos el token
        $sql = "SELECT * FROM password_resets WHERE token = :token";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['token' => $token]);
        $row = $stmt->fetch();

        if (!$row) {
            echo "Token no válido o expirado.";
            return;
        }

        // Actualizamos la contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateSql = "UPDATE User SET password = :password WHERE id = :id";
        $updateStmt = $this->db->prepare($updateSql);
        $updateStmt->execute([
            'password' => $hashedPassword,
            'id' => $row['user_id']
        ]);

        // Borramos el token
        $deleteSql = "DELETE FROM password_resets WHERE token = :token";
        $deleteStmt = $this->db->prepare($deleteSql);
        $deleteStmt->execute(['token' => $token]);

        // Redirigimos al login
        header('Location: /login');
        exit;
    } else {
        echo "Método no permitido.";
    }
}
}
