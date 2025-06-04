<?php
class AdminRepository {
    // Obtenemos la conexión desde DatabaseController
    private $pdo;

    public function __construct() {
        $this->pdo = DatabaseController::connect();
    }

    public function findAdminByUsername(string $username) {
        $stmt = $this->pdo->prepare("SELECT * FROM User WHERE username = :username AND role = 'admin'");
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Devolver un objeto anónimo con las propiedades necesarias
            return (object) [
                'id' => $row['id'],
                'username' => $row['username'],
                'password' => $row['password']
            ];
        }

        return null;
    }
    
    public function getUserCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM User");
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    public function getMessageCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM messages");
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    public function getActiveUsers($limit = 5) {
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.username, COUNT(m.id) as message_count 
            FROM User u
            LEFT JOIN messages m ON u.id = m.user_id
            GROUP BY u.id
            ORDER BY message_count DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllUsers() {
        $stmt = $this->pdo->query("
            SELECT id, username, email, role, created_at 
            FROM User 
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUsersByGender() {
        $stmt = $this->pdo->query("
            SELECT 
                CASE 
                    WHEN gender IS NULL THEN 'unknown'
                    WHEN gender = '' THEN 'unknown'
                    ELSE gender
                END as gender,
                COUNT(*) as count 
            FROM User
            GROUP BY gender
            ORDER BY count DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductCount() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM productos");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    public function getProductsByCategory() {
        $stmt = $this->pdo->prepare("
            SELECT category, COUNT(*) as count
            FROM productos
            GROUP BY category
        ");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $counts = ['hombre' => 0, 'mujer' => 0, 'todos' => 0];
        foreach ($result as $row) {
            $category = strtolower($row['category']);
            if (array_key_exists($category, $counts)) {
                $counts[$category] = (int)$row['count'];
            }
        }
        return $counts;
    }

    public function obtenerTotalCompras(): float {
        $stmt = $this->pdo->query("SELECT SUM(cantidad * precio) AS total_compras FROM pedido_detalles");
        $total = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float) ($total['total_compras'] ?? 0);
    }

    public function obtenerCantidadCompras(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) AS total_compras FROM pedido_detalles");
        $total = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($total['total_compras'] ?? 0);
    }

    public function getTotalListas(): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM listas");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['count'] ?? 0);
    }

    // También puedes incluir aquí funciones CRUD para productos si son para administración

    public function getAllProductos() {
        $stmt = $this->pdo->query("SELECT * FROM productos");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertarProducto($data, $files) {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("INSERT INTO productos (name, description, price, category, image_path) VALUES (?, ?, ?, ?, ?)");
            $defaultImagePath = '/public/images/default-product.png';
            $stmt->execute([
                $data['name'],
                $data['description'],
                $data['price'],
                $data['category'],
                $defaultImagePath
            ]);
            $producto_id = $this->pdo->lastInsertId();
            if (isset($files['images'])) {
                foreach ($files['images']['tmp_name'] as $index => $tmpName) {
                    if (is_uploaded_file($tmpName)) {
                        $imgContent = file_get_contents($tmpName);
                        $stmtImg = $this->pdo->prepare("INSERT INTO imagenes (producto_id, url) VALUES (?, ?)");
                        $stmtImg->execute([$producto_id, $imgContent]);
                        if ($index === 0) {
                            $stmtUpdate = $this->pdo->prepare("UPDATE productos SET image_path = ? WHERE id = ?");
                            $stmtUpdate->execute([
                                'imagen guardada', // O ruta real si guardas archivo físico
                                $producto_id
                            ]);
                        }
                    }
                }
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log('Error al insertar producto: ' . $e->getMessage());
            return false;
        }
    }

    public function getProductoById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarProducto($id, $data, $files) {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("UPDATE productos SET name = ?, description = ?, price = ?, category = ? WHERE id = ?");
            $stmt->execute([
                $data['name'],
                $data['description'],
                $data['price'],
                $data['category'],
                $id
            ]);
            if (isset($files['images']) && !empty($files['images']['tmp_name'][0])) {
                foreach ($files['images']['tmp_name'] as $tmpName) {
                    if (is_uploaded_file($tmpName)) {
                        $imgContent = file_get_contents($tmpName);
                        $stmtImg = $this->pdo->prepare("INSERT INTO imagenes (producto_id, url) VALUES (?, ?)");
                        $stmtImg->execute([$id, $imgContent]);
                    }
                }
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log('Error al actualizar producto: ' . $e->getMessage());
            return false;
        }
    }

    public function eliminarProducto($id) {
        // Eliminar imágenes relacionadas
        $stmt = $this->pdo->prepare("DELETE FROM imagenes WHERE producto_id = :id");
        $stmt->execute(['id' => $id]);

        // Eliminar el producto
        $stmt = $this->pdo->prepare("DELETE FROM productos WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getImagenesBase64PorProductoId($producto_id) {
        $stmt = $this->pdo->prepare("SELECT url FROM imagenes WHERE producto_id = ?");
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
