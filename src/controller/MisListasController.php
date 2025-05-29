<?php
class MisListasController {
    private $db;
    private $userId;

    public function __construct($db, $userId) {
        $this->db = $db;
        $this->userId = $userId;
    }

    public function getListas() {
        $stmt = $this->db->prepare("SELECT * FROM listas WHERE usuario_id = ?");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductosPorLista($listaId) {
        $stmt = $this->db->prepare("
            SELECT p.*
            FROM lista_productos lp
            JOIN productos p ON lp.producto_id = p.id
            WHERE lp.lista_id = ?
        ");
        $stmt->execute([$listaId]);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($productos as &$producto) {
            $producto['imagenes_base64'] = $this->getImagenesBase64($producto['id']);
        }
        return $productos;
    }

    public function crearLista($nombreLista) {
        $stmt = $this->db->prepare("INSERT INTO listas (usuario_id, nombre) VALUES (?, ?)");
        return $stmt->execute([$this->userId, $nombreLista]);
    }
    public function eliminarProductoDeLista($lista_id, $producto_id) {
    $stmt = $this->db->prepare("DELETE FROM lista_productos WHERE lista_id = ? AND producto_id = ?");
    $stmt->execute([$lista_id, $producto_id]);
}

    public function eliminarLista($lista_id) {
    // Eliminar productos asociados primero
        $stmt = $this->db->prepare("DELETE FROM lista_productos WHERE lista_id = ?");
        $stmt->execute([$lista_id]);

    // Luego eliminar la lista
        $stmt = $this->db->prepare("DELETE FROM listas WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$lista_id, $this->userId]);
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
    public function procesarFormulario() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $producto_id = $_POST['producto_id'] ?? null;
    $lista_id = $_POST['lista_id'] ?? null;
    $nombre_nueva_lista = trim($_POST['nombre_nueva_lista'] ?? '');

    if (!$producto_id) {
        return;
    }

    // Crear nueva lista si hay un nombre
    if (!empty($nombre_nueva_lista)) {
        $stmt = $this->db->prepare("INSERT INTO listas (usuario_id, nombre) VALUES (?, ?)");
        $stmt->execute([$this->userId, $nombre_nueva_lista]);
        $lista_id = $this->db->lastInsertId();
    }

    if (!$lista_id) {
        return;
    }

    // Comprobar si ya está el producto en la lista
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM lista_productos WHERE lista_id = ? AND producto_id = ?");
    $stmt->execute([$lista_id, $producto_id]);
    $existe = $stmt->fetchColumn();

    if (!$existe) {
        $stmt = $this->db->prepare("INSERT INTO lista_productos (lista_id, producto_id) VALUES (?, ?)");
        $stmt->execute([$lista_id, $producto_id]);
    }

    // Redirigir de vuelta
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

}
