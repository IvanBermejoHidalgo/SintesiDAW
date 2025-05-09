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

        return [
            'productos' => $this->getProductosPorCategoria($categoria),
            'categoria_actual' => $categoria,
            'userData' => SessionController::getUserData($_SESSION['user_id']),
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
}
