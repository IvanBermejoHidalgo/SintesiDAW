<?php

class CheckoutRepository {
    private $db;

    public function __construct() {
        $this->db = DatabaseController::connect();
    }

    public function beginTransaction() {
        $this->db->beginTransaction();
    }

    public function commit() {
        $this->db->commit();
    }

    public function rollBack() {
        $this->db->rollBack();
    }

    public function getCartItems($userId) {
        $stmt = $this->db->prepare("
            SELECT p.id, p.name, p.price, c.cantidad, c.talla
            FROM carrito c
            JOIN productos p ON c.producto_id = p.id
            WHERE c.usuario_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function guardarPedido($userId, $formData) {
        $stmt = $this->db->prepare("
            INSERT INTO pedidos (usuario_id, nombre, email, telefono, direccion, ciudad, codigo_postal, pais, metodo_pago)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $formData['name'],
            $formData['email'],
            $formData['phone'] ?? '',
            $formData['address'],
            $formData['city'],
            $formData['postal_code'],
            $formData['country'],
            $formData['payment_method']
        ]);

        return $this->db->lastInsertId();
    }

    public function guardarDetallesPedido($pedidoId, $cartItems) {
        $stmt = $this->db->prepare("
            INSERT INTO pedido_detalles (pedido_id, producto_id, talla, cantidad, precio)
            VALUES (?, ?, ?, ?, ?)
        ");
        foreach ($cartItems as $item) {
            $stmt->execute([
                $pedidoId,
                $item['id'],
                $item['talla'],
                $item['cantidad'],
                $item['price']
            ]);
        }
    }

    public function vaciarCarrito($userId) {
        $stmt = $this->db->prepare("DELETE FROM carrito WHERE usuario_id = ?");
        $stmt->execute([$userId]);
    }

    public function getPedidosPrevios($userId) {
        $stmt = $this->db->prepare("
            SELECT id, nombre, email, telefono, direccion, ciudad, codigo_postal, pais, metodo_pago
            FROM pedidos
            WHERE usuario_id = ?
            ORDER BY fecha DESC
            LIMIT 5
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
