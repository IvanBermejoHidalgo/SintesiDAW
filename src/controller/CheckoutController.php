<?php

require_once __DIR__ . '/../services/CheckoutService.php';
require_once __DIR__ . '/../repositories/CheckoutRepository.php';
require_once __DIR__ . '/../controller/SessionController.php';

class CheckoutController {
    private $twig;
    private $service;

    public function __construct($twig) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }

        $this->twig = $twig;
        $repository = new CheckoutRepository();
        $this->service = new CheckoutService($repository);
    }

    public function handleRequest() {
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = [
                'name'          => trim($_POST['name'] ?? ''),
                'email'         => trim($_POST['email'] ?? ''),
                'phone'         => trim($_POST['phone'] ?? ''),
                'address'       => trim($_POST['address'] ?? ''),
                'city'          => trim($_POST['city'] ?? ''),
                'postal_code'   => trim($_POST['postal_code'] ?? ''),
                'country'       => trim($_POST['country'] ?? ''),
                'payment_method'=> $_POST['payment_method'] ?? ''
            ];

            $result = $this->service->procesarPedido($userId, $formData);

            if (isset($result['success'])) {
                header("Location: /pedido_confirmado?id=" . $result['pedidoId']);
                exit();
            }

            $error = $result['error'] ?? 'Error inesperado.';
            return $this->renderCheckoutPage($error);
        }

        return $this->renderCheckoutPage();
    }

    private function renderCheckoutPage($error = null) {
        $userId = $_SESSION['user_id'];
        $data = $this->service->getCheckoutData($userId);

        echo $this->twig->render('tienda/checkout.html', [
            'userData'       => $data['userData'],
            'cartItems'      => $data['cartItems'],
            'total'          => $data['total'],
            'error'          => $error,
            'previousOrders' => $data['previousOrders'],
            'current_user_id'=> $userId,
            'current_page'   => 'checkout',
            'language'       => $_SESSION['language'] ?? 'es'
        ]);
    }
}
