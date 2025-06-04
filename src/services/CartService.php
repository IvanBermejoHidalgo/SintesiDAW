<?php
class CartService {
    private $repo;
    private $imageRepo;

    public function __construct() {
        $this->repo = new CartRepository();
        $this->imageRepo = new ImageRepository();
    }

    public function addToCart($userId, $data) {
        $productId = intval($data['product_id']);
        $talla = trim($data['talla']);
        $this->repo->addItem($userId, $productId, $talla);
    }

    public function removeFromCart($userId, $data) {
        $productId = intval($data['product_id']);
        $talla = $data['talla'] ?? '';
        $this->repo->removeItem($userId, $productId, $talla);
    }

    public function increment($productId, $talla, $userId) {
        $this->repo->incrementQuantity($userId, $productId, $talla);
        header("Location: /carrito");
        exit();
    }

    public function decrement($productId, $talla, $userId) {
        $this->repo->decrementQuantity($userId, $productId, $talla);
        header("Location: /carrito");
        exit();
    }

    public function getCartView($userId) {
        $items = $this->repo->getItems($userId);
        foreach ($items as &$item) {
            $imgs = $this->imageRepo->getImagenesBase64($item['id']);
            $item['image_url'] = !empty($imgs)
                ? 'data:image/jpeg;base64,' . $imgs[0]
                : '/images/default-product.png';
        }
        return [
            'cartItems' => $items,
            'userData' => SessionController::getUserData($userId)
        ];
    }

    public function getCheckout($userId) {
        $items = $this->repo->getItems($userId);
        $total = array_reduce($items, fn($sum, $i) => $sum + $i['price'] * $i['cantidad'], 0);
        return [
            'cartItems' => $items,
            'total' => $total,
            'userData' => SessionController::getUserData($userId)
        ];
    }
}
