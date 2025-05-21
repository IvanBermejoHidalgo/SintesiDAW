<?php

class CheckoutController {
    private $db;
    private $twig;

    public function __construct($twig) {
        $this->db = DatabaseController::connect();

        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit();
        }

        $this->twig = $twig;
    }

    public function handleRequest() {
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre       = trim($_POST['name'] ?? '');
            $email        = trim($_POST['email'] ?? '');
            $telefono     = trim($_POST['phone'] ?? '');
            $direccion    = trim($_POST['address'] ?? '');
            $ciudad       = trim($_POST['city'] ?? '');
            $codigoPostal = trim($_POST['postal_code'] ?? '');
            $pais         = trim($_POST['country'] ?? '');
            $metodoPago   = $_POST['payment_method'] ?? '';

            if (!$nombre || !$email || !$direccion || !$ciudad || !$codigoPostal || !$pais || !$metodoPago) {
                $error = "Por favor completa todos los campos obligatorios.";
                return $this->renderCheckoutPage($error);
            }

            $cartItems = $this->getCartItems($userId);
            if (empty($cartItems)) {
                $error = "Tu carrito está vacío.";
                return $this->renderCheckoutPage($error);
            }

            $this->db->beginTransaction();
            try {
                $pedidoId = $this->savePedido($userId, $nombre, $email, $telefono, $direccion, $ciudad, $codigoPostal, $pais, $metodoPago);
                $this->savePedidoDetalles($pedidoId, $cartItems);
                $this->clearCart($userId);
                $this->db->commit();

                header("Location: /pedido_confirmado?id=" . $pedidoId);
                exit();

            } catch (Exception $e) {
                $this->db->rollBack();
                $error = "Error al procesar el pedido. Intenta de nuevo.";
                return $this->renderCheckoutPage($error);
            }
        }

        // GET: mostrar página checkout
        return $this->renderCheckoutPage();
    }

    private function renderCheckoutPage($error = null) {
        $userId = $_SESSION['user_id'];
        $userData = SessionController::getUserData($userId);
        $cartItems = $this->getCartItems($userId);
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['price'] * $item['cantidad'];
        }

        echo $this->twig->render('checkout.html', [
            'userData' => $userData,
            'cartItems' => $cartItems,
            'total' => $total,
            'error' => $error,
            'current_user_id' => $userId,
            'current_page' => 'checkout',
            'language' => $_SESSION['language'] ?? 'es'
        ]);
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

    private function savePedido($userId, $nombre, $email, $telefono, $direccion, $ciudad, $codigoPostal, $pais, $metodoPago) {
        $stmt = $this->db->prepare("
            INSERT INTO pedidos (usuario_id, nombre, email, telefono, direccion, ciudad, codigo_postal, pais, metodo_pago)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $nombre, $email, $telefono, $direccion, $ciudad, $codigoPostal, $pais, $metodoPago]);

        return $this->db->lastInsertId();
    }

    private function savePedidoDetalles($pedidoId, $cartItems) {
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

    private function clearCart($userId) {
        $stmt = $this->db->prepare("DELETE FROM carrito WHERE usuario_id = ?");
        $stmt->execute([$userId]);
    }
}
