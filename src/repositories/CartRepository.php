<?php
class CartRepository {
    private $db;

    public function __construct() {
        $this->db = DatabaseController::connect();
    }

    public function addItem($userId, $productId, $talla) {
        $stmt = $this->db->prepare("
            SELECT cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ? AND talla = ?
        ");
        $stmt->execute([$userId, $productId, $talla]);

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $newQty = $row['cantidad'] + 1;
            $up = $this->db->prepare("
                UPDATE carrito SET cantidad = ? WHERE usuario_id = ? AND producto_id = ? AND talla = ?
            ");
            $up->execute([$newQty, $userId, $productId, $talla]);
        } else {
            $ins = $this->db->prepare("
                INSERT INTO carrito (usuario_id, producto_id, talla, cantidad) VALUES (?, ?, ?, 1)
            ");
            $ins->execute([$userId, $productId, $talla]);
        }
    }

    public function removeItem($userId, $productId, $talla) {
        $stmt = $this->db->prepare("
            DELETE FROM carrito WHERE usuario_id = ? AND producto_id = ? AND talla = ?
        ");
        $stmt->execute([$userId, $productId, $talla]);
    }

    public function incrementQuantity($userId, $productId, $talla) {
        $stmt = $this->db->prepare("
            UPDATE carrito SET cantidad = cantidad + 1 WHERE usuario_id = ? AND producto_id = ? AND talla = ?
        ");
        $stmt->execute([$userId, $productId, $talla]);
    }

    public function decrementQuantity($userId, $productId, $talla) {
        $stmt = $this->db->prepare("
            SELECT cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ? AND talla = ?
        ");
        $stmt->execute([$userId, $productId, $talla]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['cantidad'] > 1) {
            $stmt = $this->db->prepare("
                UPDATE carrito SET cantidad = cantidad - 1 WHERE usuario_id = ? AND producto_id = ? AND talla = ?
            ");
            $stmt->execute([$userId, $productId, $talla]);
        } else {
            $this->removeItem($userId, $productId, $talla);
        }
    }

    public function getItems($userId) {
        $stmt = $this->db->prepare("
            SELECT p.id, p.name, p.price, c.cantidad, c.talla
            FROM carrito c JOIN productos p ON c.producto_id = p.id
            WHERE c.usuario_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
