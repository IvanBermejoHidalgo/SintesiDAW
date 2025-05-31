<?php

require_once 'MisListasController.php'; 

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
        $misListasController = new MisListasController($this->db, $userId);
        $listas = $misListasController->getListas();

        return [
            'productos' => $this->getProductosPorCategoria($categoria),
            'categoria_actual' => $categoria,
            'buscar' => isset($_GET['buscar']) ? $_GET['buscar'] : '',
            'userData' => SessionController::getUserData($userId),
            'carrito' => $this->getProductosCarrito($userId),
            'listas' => $listas,
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
        $buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

        if ($categoria === 'todos') {
            if ($buscar !== '') {
                $stmt = $this->db->prepare("SELECT * FROM productos WHERE name LIKE ?");
                $stmt->execute(["%$buscar%"]);
            } else {
                $stmt = $this->db->query("SELECT * FROM productos");
            }
        } else {
            if ($buscar !== '') {
                $stmt = $this->db->prepare("SELECT * FROM productos WHERE category = ? AND name LIKE ?");
                $stmt->execute([$categoria, "%$buscar%"]);
            } else {
                $stmt = $this->db->prepare("SELECT * FROM productos WHERE category = ?");
                $stmt->execute([$categoria]);
            }
        }

        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                
                $path = $imagen['url'];
                if (file_exists($path)) {
                    $imgData = file_get_contents($path);
                    $imagenes_base64[] = base64_encode($imgData);
                } else {
                    $imagenes_base64[] = base64_encode($imagen['url']);
                }
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
            FROM lista_productos lp
            JOIN productos p ON lp.producto_id = p.id
            JOIN listas l ON l.id = lp.lista_id
            WHERE l.usuario_id = ?
        ");
        $stmt->execute([$usuario_id]);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($productos as &$producto) {
            $producto['imagenes_base64'] = $this->getImagenesBase64($producto['id']);
        }

        return $productos;
    }
}
