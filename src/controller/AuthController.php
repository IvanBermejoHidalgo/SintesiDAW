<?php
namespace Controller;

use services\AuthService;

class AuthController {
    public static function showResetForm() {
        self::render('reset_request.twig');
    }

    public static function handleResetRequest() {
        $email = $_POST['email'] ?? '';
        $authService = new AuthService();
        $authService->requestPasswordReset($email);
        header("Location: /reset-success");
    }

    public static function showResetPasswordForm() {
        $token = $_GET['token'] ?? '';
        self::render('reset_form.twig', ['token' => $token]);
    }

    public static function handlePasswordReset() {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $authService = new AuthService();
        $authService->resetPassword($token, $password);
        header("Location: /login");
    }

    public static function showSuccess() {
        self::render('reset_success.twig');
    }

    public function handleChangePassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $token = $_POST['token'] ?? '';

            $authService = new AuthService();
            $result = $authService->changePassword($password, $confirmPassword, $token);

            if ($result === "success") {
                header("Location: /login");
                exit;
            } else {
                echo $result;
            }
        } else {
            echo "Método no permitido.";
        }
    }

    private static function render(string $template, array $data = []) {
        include_once '../vendor/autoload.php';
        $loader = new \Twig\Loader\FilesystemLoader('../public/views');
        $twig = new \Twig\Environment($loader);
        echo $twig->render($template, $data);
    }
}
