<?php

class PedidoConfirmadoController {
    private $db;
    private $twig;

    public function __construct($twig) {
        $this->db = DatabaseController::connect();

        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }

        $this->twig = $twig;
    }

    public function handleRequest() {
        $userId = $_SESSION['user_id'];

        $pedidoId = $_GET['id'] ?? null;

        if (!$pedidoId || !is_numeric($pedidoId)) {
            header("Location: /tienda");
            exit();
        }

        $pedido = $this->getPedido($pedidoId, $userId);
        if (!$pedido) {
            header("Location: /tienda");
            exit();
        }

        $detalles = $this->getDetallesPedido($pedidoId);
        $userData = SessionController::getUserData($userId);

        $total = 0;
        foreach ($detalles as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

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

    private function getPedido($pedidoId, $userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM pedidos 
            WHERE id = ? AND usuario_id = ?
        ");
        $stmt->execute([$pedidoId, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getDetallesPedido($pedidoId) {
        $stmt = $this->db->prepare("
            SELECT p.name, d.talla, d.cantidad, d.precio
            FROM pedido_detalles d
            JOIN productos p ON d.producto_id = p.id
            WHERE d.pedido_id = ?
        ");
        $stmt->execute([$pedidoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
