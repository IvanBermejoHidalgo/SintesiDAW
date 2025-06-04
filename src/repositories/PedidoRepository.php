<?php

class PedidoRepository {
    private $db;

    public function __construct() {
        $this->db = DatabaseController::connect();
    }

    public function getPedidoByIdAndUser($pedidoId, $userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM pedidos 
            WHERE id = ? AND usuario_id = ?
        ");
        $stmt->execute([$pedidoId, $userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getDetallesPedido($pedidoId) {
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
