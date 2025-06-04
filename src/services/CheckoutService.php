<?php

class CheckoutService {
    private $repository;

    public function __construct(CheckoutRepository $repository) {
        $this->repository = $repository;
    }

    public function procesarPedido($userId, $formData) {
        $camposObligatorios = ['name', 'email', 'address', 'city', 'postal_code', 'country', 'payment_method'];
        foreach ($camposObligatorios as $campo) {
            if (empty($formData[$campo])) {
                return ['error' => "Por favor completa todos los campos obligatorios."];
            }
        }

        $cartItems = $this->repository->getCartItems($userId);
        if (empty($cartItems)) {
            return ['error' => "Tu carrito está vacío."];
        }

        try {
            $this->repository->beginTransaction();
            $pedidoId = $this->repository->guardarPedido($userId, $formData);
            $this->repository->guardarDetallesPedido($pedidoId, $cartItems);
            $this->repository->vaciarCarrito($userId);
            $this->repository->commit();
            return ['success' => true, 'pedidoId' => $pedidoId];
        } catch (Exception $e) {
            $this->repository->rollBack();
            return ['error' => "Error al procesar el pedido. Intenta de nuevo."];
        }
    }

    public function getCheckoutData($userId) {
        $cartItems = $this->repository->getCartItems($userId);
        $total = array_reduce($cartItems, function ($carry, $item) {
            return $carry + ($item['price'] * $item['cantidad']);
        }, 0);

        $previousOrders = $this->repository->getPedidosPrevios($userId);
        $userData = SessionController::getUserData($userId);

        return [
            'cartItems' => $cartItems,
            'total' => $total,
            'userData' => $userData,
            'previousOrders' => $previousOrders,
        ];
    }
}
