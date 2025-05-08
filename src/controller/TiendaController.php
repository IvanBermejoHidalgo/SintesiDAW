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
        // Extrae la ruta actual desde la URL
        $uri = $_SERVER['REQUEST_URI'];
        if (preg_match('#^/tienda/(hombre|mujer|todos)$#', $uri, $matches)) {
            return $matches[1]; // hombre, mujer o todos
        }
        return 'todos'; // por defecto
    }

    private function getProductosPorCategoria($categoria) {
        if ($categoria === 'todos') {
            $stmt = $this->db->query("SELECT * FROM productos");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $stmt = $this->db->prepare("SELECT * FROM productos WHERE category = ?");
        $stmt->execute([$categoria]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
