<?php

class TiendaController {
    private $db;

    public function __construct() {
        $this->db = DatabaseController::connect();
    }

    public function handleRequest() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /");
            exit();
        }

        $categoria = $this->getCategoriaDesdeRuta();
        $userId = $_SESSION['user_id'];

        return [
            'productos' => $this->getProductosPorCategoria($categoria),
            'categoria_actual' => $categoria,
            'userData' => SessionController::getUserData($userId),
            'carrito' => $this->getProductosCarrito($userId),
            'listas' => $this->getProductosLista($userId),
            'current_page' => 'tienda/' . $categoria
        ];
    }

    private function getCategoriaDesdeRuta() {
        $uri = $_SERVER['REQUEST_URI'];
        if (preg_match('#^/tienda/(hombre|mujer|todos)$#', $uri, $matches)) {
            return $matches[1];
        }
        return 'todos';
    }

    private function getProductosPorCategoria($categoria) {
        if ($categoria === 'todos') {
            $stmt = $this->db->query("SELECT * FROM productos");
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM productos WHERE category = ?");
            $stmt->execute([$categoria]);
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        foreach ($productos as &$producto) {
            $producto['imagenes_base64'] = $this->getImagenesBase64($producto['id']);
        }

        return $productos;
    }

    public function getProductoPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
            $producto['imagenes_base64'] = $this->getImagenesBase64($producto['id']);
        }

        return $producto;
    }

    private function getImagenesBase64($producto_id) {
        $stmt = $this->db->prepare("SELECT url FROM imagenes WHERE producto_id = ?");
        $stmt->execute([$producto_id]);
        $imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $imagenes_base64 = [];
        foreach ($imagenes as $imagen) {
            if (!empty($imagen['url'])) {
                $imagenes_base64[] = base64_encode($imagen['url']);
            }
        }

        return $imagenes_base64;
    }

    private function getProductosCarrito($usuario_id) {
        $stmt = $this->db->prepare("
            SELECT p.*, c.cantidad 
            FROM carrito c 
            JOIN productos p ON c.producto_id = p.id 
            WHERE c.usuario_id = ?
        ");
        $stmt->execute([$usuario_id]);
        $carrito = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($carrito as &$producto) {
            $producto['imagenes_base64'] = $this->getImagenesBase64($producto['id']);
        }

        return $carrito;
    }

    private function getProductosLista($usuario_id) {
        $stmt = $this->db->prepare("
            SELECT p.* 
            FROM listas l 
            JOIN productos p ON l.producto_id = p.id 
            WHERE l.usuario_id = ?
        ");
        $stmt->execute([$usuario_id]);
        $listas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($listas as &$producto) {
            $producto['imagenes_base64'] = $this->getImagenesBase64($producto['id']);
        }

        return $listas;
    }
}
