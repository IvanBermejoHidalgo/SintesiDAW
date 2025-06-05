<?php
require_once __DIR__ . '/../services/ProfileService.php';

class ProfileController {
    private ProfileService $service;

    public function __construct() {
        $this->service = new ProfileService();
    }

    public function handleRequest() {
        $userId = $_GET['id'] ?? $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (isset($_POST['update_profile'])) {
                    $changes = $this->service->updateProfile($userId, $_POST, $_FILES);
                    $_SESSION[$changes ? 'success' : 'info'] = $changes ? "Perfil actualizado correctamente" : "No se realizaron cambios";
                }

                if (isset($_POST['delete_account'])) {
                    $success = $this->service->deleteAccount($userId, $_POST['password']);
                    if ($success) {
                        session_destroy();
                        header("Location: /");
                        exit;
                    } else {
                        $_SESSION['error'] = "Error al eliminar la cuenta.";
                        header("Location: /profile");
                        exit;
                    }
                }
            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }

            header("Location: /profile");
            exit;
        }

        $data = $this->service->getProfileData($userId, $_SESSION['user_id']);
        if (!$data) {
            header("Location: /home");
            exit;
        }

        return $data;
    }

    public function handlePublicProfileRequest($userId) {
        $data = $this->service->getProfileData($userId, $_SESSION['user_id'] ?? null);
        if (!$data) {
            header("Location: /home");
            exit;
        }

        return $data;
    }
}
