<?php
require_once __DIR__ . '/../services/HomeService.php';

class HomeController {
    private HomeService $service;

    public function __construct() {
        $this->service = new HomeService();
    }

    public function handleRequest() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /");
            exit();
        }

        $this->service->handlePostActions();

        return [
            'messages' => $this->service->getAllMessagesWithUsers(),
            'userData' => SessionController::getUserData($_SESSION['user_id']),
            'current_user_id' => $_SESSION['user_id']
        ];
    }

    public function getUserById(int $id): ?array {
        return $this->service->getUserById($id);
    }
}
