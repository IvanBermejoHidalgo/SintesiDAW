<?php

class PedidosRepository {
    private $db;

    public function __construct() {
        $this->db = DatabaseController::connect();
    }

    public function fetchPedidos($userId) {
        $stmt = $this->db->prepare("
            SELECT id, fecha, metodo_pago, nombre, email, telefono, direccion, ciudad, codigo_postal, pais
            FROM pedidos
            WHERE usuario_id = ?
            ORDER BY fecha DESC
        ");
        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchDetallesPedido($pedidoId) {
        $stmt = $this->db->prepare("
            SELECT pd.cantidad, pd.talla, pd.precio, p.name
            FROM pedido_detalles pd
            JOIN productos p ON pd.producto_id = p.id
            WHERE pd.pedido_id = ?
        ");
        $stmt->execute([$pedidoId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
