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
                $productId = intval($_POST['product_id']);
$talla     = $_POST['talla'] ?? '';
$this->removeFromCart($userId, $productId, $talla);

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

    private function removeFromCart($userId, $productId, $talla) {
    $del = $this->db->prepare("
        DELETE FROM carrito 
        WHERE usuario_id = ? AND producto_id = ? AND talla = ?
    ");
    $del->execute([$userId, $productId, $talla]);
}

    public function incrementar($productId, $talla) {
    $userId = $_SESSION['user_id'];
    $stmt = $this->db->prepare("
        UPDATE carrito
        SET cantidad = cantidad + 1
        WHERE usuario_id = ? AND producto_id = ? AND talla = ?
    ");
    $stmt->execute([$userId, $productId, $talla]);

    header("Location: /carrito");
    exit();
}

public function decrementar($productId, $talla) {
    $userId = $_SESSION['user_id'];

    $stmt = $this->db->prepare("
        SELECT cantidad FROM carrito 
        WHERE usuario_id = ? AND producto_id = ? AND talla = ?
    ");
    $stmt->execute([$userId, $productId, $talla]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        if ($row['cantidad'] > 1) {
            $update = $this->db->prepare("
                UPDATE carrito 
                SET cantidad = cantidad - 1 
                WHERE usuario_id = ? AND producto_id = ? AND talla = ?
            ");
            $update->execute([$userId, $productId, $talla]);
        } else {
            $delete = $this->db->prepare("
                DELETE FROM carrito 
                WHERE usuario_id = ? AND producto_id = ? AND talla = ?
            ");
            $delete->execute([$userId, $productId, $talla]);
        }
    }

    header("Location: /carrito");
    exit();
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
    public function handleCheckout() {
        $userId = $_SESSION['user_id'];
        $userData = SessionController::getUserData($userId);
        $cartItems = $this->getCartItems($userId);
    
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['price'] * $item['cantidad'];
        }
    
        return [
            'cartItems' => $cartItems,
            'total' => $total,
            'userData' => $userData
        ];
    }
    
}
