<?php

class MisListasRepository {
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

    public function eliminarProductoDeLista($lista_id, $producto_id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM lista_productos WHERE lista_id = ? AND producto_id = ?");
            $stmt->execute([$lista_id, $producto_id]);
            
            return [
                'success' => true,
                'message' => 'Producto eliminado de la lista correctamente'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar el producto: ' . $e->getMessage()
            ];
        }
    }

    public function eliminarLista($lista_id) {
        try {
            $this->db->beginTransaction();

            // Eliminar referencias en shared_lists para evitar error FK
            $stmt = $this->db->prepare("DELETE FROM shared_lists WHERE lista_id = ?");
            $stmt->execute([$lista_id]);

            // Eliminar productos asociados a la lista
            $stmt = $this->db->prepare("DELETE FROM lista_productos WHERE lista_id = ?");
            $stmt->execute([$lista_id]);

            // Eliminar la lista solo si pertenece al usuario
            $stmt = $this->db->prepare("DELETE FROM listas WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$lista_id, $this->userId]);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Lista eliminada correctamente'
            ];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error al eliminar la lista: ' . $e->getMessage()
            ];
        }
    }

    public function crearLista($nombreLista) {
        try {
            $stmt = $this->db->prepare("INSERT INTO listas (usuario_id, nombre) VALUES (?, ?)");
            $success = $stmt->execute([$this->userId, $nombreLista]);

            return [
                'success' => $success,
                'message' => $success ? 'Lista creada correctamente' : 'Error al crear la lista'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al crear la lista: ' . $e->getMessage()
            ];
        }
    }

    public function getUltimaListaId() {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM listas WHERE usuario_id = ?");
        $stmt->execute([$this->userId]);
        return $stmt->fetchColumn();
    }

    public function existeProductoEnLista($lista_id, $producto_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM lista_productos WHERE lista_id = ? AND producto_id = ?");
        $stmt->execute([$lista_id, $producto_id]);
        return $stmt->fetchColumn() > 0;
    }

    public function agregarProductoALista($lista_id, $producto_id) {
        $stmt = $this->db->prepare("INSERT INTO lista_productos (lista_id, producto_id) VALUES (?, ?)");
        $stmt->execute([$lista_id, $producto_id]);
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
}
