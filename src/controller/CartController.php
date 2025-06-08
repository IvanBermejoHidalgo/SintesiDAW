<?php

require_once __DIR__ . '/../services/CartService.php';
require_once __DIR__ . '/../repositories/CartRepository.php';
require_once __DIR__ . '/../repositories/ImageRepository.php';

class CartController {
    private $service;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /");
            exit();
        }
        $this->service = new CartService();
    }

    public function handleRequest() {
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['add_to_cart'])) {
                $this->service->addToCart($userId, $_POST);
            } elseif (isset($_POST['remove_from_cart'])) {
                $this->service->removeFromCart($userId, $_POST);
            }
            header("Location: /carrito");
            exit();
        }

        return $this->service->getCartView($userId);
    }

    public function incrementar($productId, $talla) {
    header('Content-Type: application/json');
    $userId = $_SESSION['user_id'];

    $item = $this->service->increment($productId, $talla, $userId);
    $total = $this->service->getCartTotal($userId);

    echo json_encode([
        'success' => true,
        'item' => $item,
        'total' => $total
    ]);
    exit();
}

public function decrementar($productId, $talla) {
    header('Content-Type: application/json');
    $userId = $_SESSION['user_id'];

    $item = $this->service->decrement($productId, $talla, $userId);
    $total = $this->service->getCartTotal($userId);

    echo json_encode([
        'success' => true,
        'item' => $item,
        'total' => $total
    ]);
    exit();
}

public function eliminar($productId, $talla) {
    header('Content-Type: application/json');
    $userId = $_SESSION['user_id'];

    $this->service->removeFromCart($userId, [
        'product_id' => $productId,
        'talla' => $talla
    ]);

    $total = $this->service->getCartTotal($userId);

    echo json_encode([
        'success' => true,
        'total' => $total
    ]);
    exit();
}



    public function handleCheckout() {
        return $this->service->getCheckout($_SESSION['user_id']);
    }
}

