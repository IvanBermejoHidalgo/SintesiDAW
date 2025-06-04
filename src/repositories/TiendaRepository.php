<?php

class TiendaRepository {
    private $db;

    public function __construct() {
        $this->db = DatabaseController::connect();
    }

    public function getProductosPorCategoria($categoria, $buscar) {
        if ($categoria === 'todos') {
            if ($buscar !== '') {
                $stmt = $this->db->prepare("SELECT * FROM productos WHERE name LIKE ?");
                $stmt->execute(["%$buscar%"]);
            } else {
                $stmt = $this->db->query("SELECT * FROM productos");
            }
        } else {
            if ($buscar !== '') {
                $stmt = $this->db->prepare("SELECT * FROM productos WHERE category = ? AND name LIKE ?");
                $stmt->execute([$categoria, "%$buscar%"]);
            } else {
                $stmt = $this->db->prepare("SELECT * FROM productos WHERE category = ?");
                $stmt->execute([$categoria]);
            }
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductoPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getProductosCarrito($usuario_id) {
        $stmt = $this->db->prepare("
            SELECT p.*, c.cantidad 
            FROM carrito c 
            JOIN productos p ON c.producto_id = p.id 
            WHERE c.usuario_id = ?
        ");
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
