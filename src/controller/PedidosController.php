<?php

class PedidosController {
    private $db;

    public function __construct() {
        $this->db = DatabaseController::connect();
        if (!isset($_SESSION['user_id'])) {
            header("Location: /");
            exit();
        }
    }

    public function handleRequest() {
        $userId = $_SESSION['user_id'];

        $pedidos = $this->getPedidosUsuario($userId);

        return [
            'pedidos' => $pedidos,
            'userData' => SessionController::getUserData($userId),
        ];
    }

    private function getPedidosUsuario($userId) {
        $stmt = $this->db->prepare("
            SELECT id, fecha, metodo_pago, nombre, email, telefono, direccion, ciudad, codigo_postal, pais
            FROM pedidos
            WHERE usuario_id = ?
            ORDER BY fecha DESC
        ");
        $stmt->execute([$userId]);
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pedidos as &$pedido) {
            $stmtItems = $this->db->prepare("
                SELECT pd.cantidad, pd.talla, pd.precio, p.name
                FROM pedido_detalles pd
                JOIN productos p ON pd.producto_id = p.id
                WHERE pd.pedido_id = ?
            ");
            $stmtItems->execute([$pedido['id']]);
            $pedido['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            // Calcular total del pedido sumando precio*cantidad
            $total = 0;
            foreach ($pedido['items'] as $item) {
                $total += $item['precio'] * $item['cantidad'];
            }
            $pedido['total_amount'] = $total;
        }

        return $pedidos;
    }
}
