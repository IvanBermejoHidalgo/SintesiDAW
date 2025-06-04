<?php

require_once __DIR__ . '/../services/PedidosService.php';
require_once __DIR__ . '/../repositories/PedidosRepository.php';

class PedidosController {
    private $service;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /");
            exit();
        }

        $repository = new PedidosRepository();
        $this->service = new PedidosService($repository);
    }

    public function handleRequest() {
        $userId = $_SESSION['user_id'];

        $pedidos = $this->service->getPedidosUsuario($userId);

        return [
            'pedidos' => $pedidos,
            'userData' => SessionController::getUserData($userId),
        ];
    }
}
