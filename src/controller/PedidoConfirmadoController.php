<?php

require_once __DIR__ . '/../services/PedidoConfirmadoService.php';
require_once __DIR__ . '/../repositories/PedidoRepository.php';

class PedidoConfirmadoController {
    private $twig;
    private $service;

    public function __construct($twig) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }

        $this->twig = $twig;
        $this->service = new PedidoConfirmadoService(new PedidoRepository());
    }

    public function handleRequest() {
        $userId = $_SESSION['user_id'];
        $pedidoId = $_GET['id'] ?? null;

        if (!$pedidoId || !is_numeric($pedidoId)) {
            header("Location: /tienda");
            exit();
        }

        $pedido = $this->service->getPedido($pedidoId, $userId);
        if (!$pedido) {
            header("Location: /tienda");
            exit();
        }

        $detalles = $this->service->getDetallesPedido($pedidoId);

        $total = array_reduce($detalles, fn($sum, $item) => $sum + $item['precio'] * $item['cantidad'], 0);

        $homeController = new HomeController();
        $userData = $homeController->getUserById($userId);

        echo $this->twig->render('tienda/pedido_confirmado.html', [
            'pedido' => $pedido,
            'orderItems' => $detalles,
            'total' => $total,
            'userData' => $userData,
            'current_page' => 'pedido_confirmado',
            'current_user_id' => $userId,
            'language' => $_SESSION['language'] ?? 'es'
        ]);
    }
}
