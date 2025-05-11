<?php

class CartController {
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add_to_cart'])) {
                $productId = intval($_POST['product_id']);
                $talla     = trim($_POST['talla']);
                $this->addToCart($userId, $productId, $talla);
            } elseif (isset($_POST['remove_from_cart'])) {
                $this->removeFromCart($userId, intval($_POST['product_id']));
            }
            header("Location: /carrito");
            exit();
        }

        $items = $this->getCartItems($userId);

        return [
            'cartItems' => $items,
            'userData'  => SessionController::getUserData($userId),
        ];
    }

    private function addToCart($userId, $productId, $talla) {
        // Verifica si ya existe el mismo producto+talla
        $stmt = $this->db->prepare("
            SELECT cantidad 
            FROM carrito 
            WHERE usuario_id = ? AND producto_id = ? AND talla = ?
        ");
        $stmt->execute([$userId, $productId, $talla]);

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Si existe, incrementa cantidad
            $newQty = $row['cantidad'] + 1;
            $up = $this->db->prepare("
                UPDATE carrito 
                SET cantidad = ? 
                WHERE usuario_id = ? AND producto_id = ? AND talla = ?
            ");
            $up->execute([$newQty, $userId, $productId, $talla]);
        } else {
            // Si no, inserta un nuevo registro
            $ins = $this->db->prepare("
                INSERT INTO carrito (usuario_id, producto_id, talla, cantidad)
                VALUES (?, ?, ?, 1)
            ");
            $ins->execute([$userId, $productId, $talla]);
        }
    }

    private function removeFromCart($userId, $productId) {
        $del = $this->db->prepare("
            DELETE FROM carrito 
            WHERE usuario_id = ? AND producto_id = ?
        ");
        $del->execute([$userId, $productId]);
    }

    private function getCartItems($userId) {
        $stmt = $this->db->prepare("
            SELECT p.id, p.name, p.price, c.cantidad, c.talla
            FROM carrito c
            JOIN productos p ON c.producto_id = p.id
            WHERE c.usuario_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
