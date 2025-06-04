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
        $this->service->increment($productId, $talla, $_SESSION['user_id']);
    }

    public function decrementar($productId, $talla) {
        $this->service->decrement($productId, $talla, $_SESSION['user_id']);
    }

    public function handleCheckout() {
        return $this->service->getCheckout($_SESSION['user_id']);
    }
}

