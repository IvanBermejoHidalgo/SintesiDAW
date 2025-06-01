<?php

// General singleton class.

class DatabaseController {
  private static $host = "localhost";
  private static $username = "usuario";
  private static $password = "usuario";
  private static $dbname = "ShopList";
  private static $options = [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
      PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
  ];
  private static $connection = null;

  public static function connect() {
      if (self::$connection === null) {
          self::$connection = new PDO(
              'mysql:host='.self::$host.';dbname='.self::$dbname, 
              self::$username, 
              self::$password, 
              self::$options
          );
      }
      return self::$connection;
  }

  public static function getAllMessages()
  {
      $pdo = self::connect();
      $query = "SELECT m.*, u.username, u.profile_image 
                FROM messages m
                JOIN User u ON m.user_id = u.id
                ORDER BY m.created_at DESC
                LIMIT 50";
      
      $stmt = $pdo->query($query);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

    public static function postNewMessage($userId, $content)
    {
        $pdo = self::connect();
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, content, created_at) VALUES (?, ?, NOW())");
        return $stmt->execute([$userId, $content]);
    }

    public static function deleteMessage($userId, $messageId) {
        $pdo = self::connect();
        
        // Start transaction
        $pdo->beginTransaction();
        
        try {
            // First delete all likes for this message
            $stmt = $pdo->prepare("DELETE FROM likes WHERE message_id = ?");
            $stmt->execute([$messageId]);
            
            // Then delete all comments for this message
            $stmt = $pdo->prepare("DELETE FROM comments WHERE message_id = ?");
            $stmt->execute([$messageId]);
            
            // Finally delete the message itself
            $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ? AND user_id = ?");
            $stmt->execute([$messageId, $userId]);
            
            // Commit transaction if all operations succeeded
            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            // Rollback on any error
            $pdo->rollBack();
            error_log("Error deleting message: " . $e->getMessage());
            return false;
        }
    }

    public static function addLike($userId, $messageId) {
      $pdo = self::connect();
      try {
          $stmt = $pdo->prepare("INSERT INTO likes (user_id, message_id) VALUES (?, ?)");
          return $stmt->execute([$userId, $messageId]);
      } catch (PDOException $e) {
          // Evita errores si ya dio like
          return false;
      }
    }

    public static function removeLike($userId, $messageId) {
        $pdo = self::connect();
        $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND message_id = ?");
        return $stmt->execute([$userId, $messageId]);
    }

    public static function getLikesCount($messageId) {
        $pdo = self::connect();
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM likes WHERE message_id = ?");
        $stmt->execute([$messageId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    public static function hasLiked($userId, $messageId) {
        $pdo = self::connect();
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM likes WHERE user_id = ? AND message_id = ?");
        $stmt->execute([$userId, $messageId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    }

    public static function addComment($userId, $messageId, $content) {
      $pdo = self::connect();
      $stmt = $pdo->prepare("INSERT INTO comments (user_id, message_id, content) VALUES (?, ?, ?)");
      return $stmt->execute([$userId, $messageId, $content]);
  }

  public static function getComments($messageId) {
      $pdo = self::connect();
      $stmt = $pdo->prepare("
          SELECT c.*, u.username, u.profile_image 
          FROM comments c
          JOIN User u ON c.user_id = u.id
          WHERE c.message_id = ?
          ORDER BY c.created_at ASC
      ");
      $stmt->execute([$messageId]);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function deleteComment($userId, $commentId) {
      $pdo = self::connect();
      $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
      return $stmt->execute([$commentId, $userId]);
  }

    public static function getUserById($userId) {
        $pdo = self::connect();
        $stmt = $pdo->prepare("
            SELECT id, username, email, password, profile_image, created_at 
            FROM User 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getMessagesByUser($userId, $currentUserId = null) {
        $pdo = self::connect();
        
        $query = "
            SELECT m.*, u.username, u.profile_image,
            (SELECT COUNT(*) FROM likes l WHERE l.message_id = m.id) as like_count,
            (SELECT COUNT(*) FROM comments c WHERE c.message_id = m.id) as comment_count";
        
        if ($currentUserId !== null) {
            $query .= ",
            EXISTS(SELECT 1 FROM likes l WHERE l.message_id = m.id AND l.user_id = :current_user) as has_liked";
        }
        
        $query .= "
            FROM messages m
            JOIN User u ON m.user_id = u.id
            WHERE m.user_id = :user_id
            ORDER BY m.created_at DESC";
        
        $stmt = $pdo->prepare($query);
        
        $params = [':user_id' => $userId];
        if ($currentUserId !== null) {
            $params[':current_user'] = $currentUserId;
        }
        
        $stmt->execute($params);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener comentarios para cada mensaje si es necesario
        foreach ($messages as &$message) {
            $message['comments'] = self::getComments($message['id']);
        }
        
        return $messages;
    }

    public static function getCommentCountByUser($userId) {
        $pdo = self::connect();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM comments 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    public static function getLikeCountByUser($userId) {
        $pdo = self::connect();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM likes 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    public static function updateProfileImage($userId, $imagePath) {
        $pdo = self::connect();
        $stmt = $pdo->prepare("
            UPDATE User 
            SET profile_image = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$imagePath, $userId]);
    }

    public static function updateUsername($userId, $newUsername) {
        $pdo = self::connect();
        $stmt = $pdo->prepare("UPDATE User SET username = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$newUsername, $userId]);
    }

    public static function getUserCount() {
        $pdo = self::connect();
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM User");
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
    
    public static function getMessageCount() {
        $pdo = self::connect();
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM messages");
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
    
    public static function getActiveUsers($limit = 5) {
        $pdo = self::connect();
        $stmt = $pdo->prepare("
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
    
    public static function getAllUsers() {
        $pdo = self::connect();
        $stmt = $pdo->query("
            SELECT id, username, email, role, created_at 
            FROM User 
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUsersByGender() {
        $pdo = self::connect();
        $stmt = $pdo->query("
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

    public static function getProductCount() {
        $pdo = self::connect();
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM productos");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }



    public static function getProductsByCategory() {
        $pdo = self::connect();
        $stmt = $pdo->prepare("
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

    public static function obtenerTotalCompras(): float {
        $pdo = self::connect();
        $stmt = $pdo->query("SELECT SUM(cantidad * precio) AS total_compras FROM pedido_detalles");
        $total = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float) ($total['total_compras'] ?? 0);
    }

    public static function obtenerCantidadCompras(): int {
        $pdo = self::connect();
        $stmt = $pdo->query("SELECT COUNT(*) AS total_compras FROM pedido_detalles");
        $total = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($total['total_compras'] ?? 0);
    }
    
    public static function getTotalListas(): int {
        $pdo = self::connect();
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM listas");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['count'] ?? 0);
    }


    public static function deleteUserAccount($userId) {
        $pdo = self::connect();
        
        // Iniciar transacción para asegurar la integridad de los datos
        $pdo->beginTransaction();
        error_log("Iniciando eliminación de cuenta para usuario ID: $userId");
        
        try {
             // 1. Eliminar likes del usuario
            error_log("Eliminando likes...");
            $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // 2. Eliminar comentarios del usuario
            error_log("Eliminando comentarios...");
            $stmt = $pdo->prepare("DELETE FROM comments WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // 3. Eliminar mensajes del usuario (esto eliminará automáticamente los likes y comentarios asociados)
            $stmt = $pdo->prepare("DELETE FROM messages WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // 4. Eliminar elementos del carrito del usuario
            $stmt = $pdo->prepare("DELETE FROM carrito WHERE usuario_id = ?");
            $stmt->execute([$userId]);
            
            // 5. Eliminar listas del usuario y sus productos asociados
            // Primero obtenemos las listas del usuario
            $stmt = $pdo->prepare("SELECT id FROM listas WHERE usuario_id = ?");
            $stmt->execute([$userId]);
            $listas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($listas)) {
                // Eliminar productos de las listas
                $placeholders = implode(',', array_fill(0, count($listas), '?'));
                $stmt = $pdo->prepare("DELETE FROM lista_productos WHERE lista_id IN ($placeholders)");
                $stmt->execute($listas);
                
                // Eliminar las listas
                $stmt = $pdo->prepare("DELETE FROM listas WHERE usuario_id = ?");
                $stmt->execute([$userId]);
            }
            
            // 6. Eliminar pedidos del usuario
            // Primero obtenemos los pedidos del usuario
            $stmt = $pdo->prepare("SELECT id FROM pedidos WHERE usuario_id = ?");
            $stmt->execute([$userId]);
            $pedidos = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($pedidos)) {
                // Eliminar detalles de pedidos
                $placeholders = implode(',', array_fill(0, count($pedidos), '?'));
                $stmt = $pdo->prepare("DELETE FROM pedido_detalles WHERE pedido_id IN ($placeholders)");
                $stmt->execute($pedidos);
                
                // Eliminar los pedidos
                $stmt = $pdo->prepare("DELETE FROM pedidos WHERE usuario_id = ?");
                $stmt->execute([$userId]);
            }
            
            // 7. Eliminar tokens de reseteo de contraseña
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $stmt->execute([$userId]);
            
             // 8. Finalmente, eliminar al usuario
            error_log("Eliminando usuario...");
            $stmt = $pdo->prepare("DELETE FROM User WHERE id = ?");
            $stmt->execute([$userId]);
        
            
            // Confirmar todas las operaciones
            $pdo->commit();
            error_log("Cuenta eliminada exitosamente");
            
            // Eliminar imagen de perfil si no es la predeterminada
            $user = self::getUserById($userId);
            if ($user && $user['profile_image'] && $user['profile_image'] !== '/images/default-profile.png') {
                $imagePath = $_SERVER['DOCUMENT_ROOT'] . $user['profile_image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            return true;
        } catch (PDOException $e) {
            // Revertir en caso de error
            $pdo->rollBack();
            error_log("Error al eliminar cuenta de usuario: " . $e->getMessage());
            return false;
        }
    }

    public static function getAllProductos() {
        $conn = self::connect();
        $stmt = $conn->query("SELECT * FROM productos");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getImagenPorProductoId($productoId) {
        $pdo = self::connect();
        $stmt = $pdo->prepare("SELECT url FROM imagenes WHERE producto_id = ? LIMIT 1");
        $stmt->execute([$productoId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row['url']; // Es binario, se puede enviar directamente
        }

        return null;
    }

    public static function insertarProducto($data, $files) {
        $pdo = self::connect();

        try {
            $pdo->beginTransaction();
            // Insertar producto
            $stmt = $pdo->prepare("INSERT INTO productos (name, description, price, category, image_path) VALUES (?, ?, ?, ?, ?)");
            // Guardamos la primera imagen como image_path para mostrar en listados (opcional)
            $defaultImagePath = '/public/images/default-product.png';
            $stmt->execute([
                $data['name'],
                $data['description'],
                $data['price'],
                $data['category'],
                $defaultImagePath
            ]);

            $producto_id = $pdo->lastInsertId();

            // Procesar imágenes
            if (isset($files['images'])) {
                foreach ($files['images']['tmp_name'] as $index => $tmpName) {
                    if (is_uploaded_file($tmpName)) {
                        $imgContent = file_get_contents($tmpName);

                        $stmtImg = $pdo->prepare("INSERT INTO imagenes (producto_id, url) VALUES (?, ?)");
                        $stmtImg->execute([
                            $producto_id,
                            $imgContent
                        ]);

                        // Para la primera imagen, actualizar la columna image_path con una ruta simulada (o en caso real, guarda la ruta del archivo en servidor)
                        if ($index === 0) {
                            // Podrías guardar la imagen en el sistema de archivos y luego guardar la ruta en image_path
                            // Por simplicidad, actualizo con 'imagen guardada' (puedes mejorar esta parte)
                            $stmtUpdate = $pdo->prepare("UPDATE productos SET image_path = ? WHERE id = ?");
                            $stmtUpdate->execute([
                                'imagen guardada', // Aquí va la ruta si la guardas físicamente
                                $producto_id
                            ]);
                        }
                    }
                }
            }

            $pdo->commit();
            return true;

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('Error al insertar producto: ' . $e->getMessage());
            return false;
        }
    }

    // Obtener un producto por ID
    public static function getProductoById(int $id) {
        $db = self::connect();
        $stmt = $db->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar producto existente
    public static function actualizarProducto($id, $data, $files) {
    $pdo = self::connect();

    try {
        $pdo->beginTransaction();

        // Actualizar datos básicos del producto
        $stmt = $pdo->prepare("UPDATE productos SET name = ?, description = ?, price = ?, category = ? WHERE id = ?");
        $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['category'],
            $id
        ]);

        // Procesar nuevas imágenes si se subieron
        if (isset($files['images']) && !empty($files['images']['tmp_name'][0])) {
            // Opcional: Eliminar imágenes existentes (descomentar si quieres esto)
            // $stmtDelImg = $pdo->prepare("DELETE FROM imagenes WHERE producto_id = ?");
            // $stmtDelImg->execute([$id]);

            foreach ($files['images']['tmp_name'] as $index => $tmpName) {
                if (is_uploaded_file($tmpName)) {
                    $imgContent = file_get_contents($tmpName);

                    $stmtImg = $pdo->prepare("INSERT INTO imagenes (producto_id, url) VALUES (?, ?)");
                    $stmtImg->execute([
                        $id,
                        $imgContent
                    ]);

                    // Actualizar la imagen principal si es la primera
                    if ($index === 0) {
                        $stmtUpdate = $pdo->prepare("UPDATE productos SET image_path = ? WHERE id = ?");
                    }
                }
            }
        }

        $pdo->commit();
        return true;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Error al actualizar producto: ' . $e->getMessage());
        return false;
    }
}


    // Eliminar producto por ID
    public static function eliminarProducto(int $id) {
        $db = self::connect();
        $stmt = $db->prepare("DELETE FROM productos WHERE id = ?");
        return $stmt->execute([$id]);
    }
    public static function addProductToList($listaId, $productoId) {
        $pdo = self::connect();
        try {
            // Verificar que no exista ya la relación para evitar duplicados
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM lista_productos WHERE lista_id = ? AND producto_id = ?");
            $stmt->execute([$listaId, $productoId]);
            if ($stmt->fetchColumn() > 0) {
                return false; // Ya existe esta relación
            }
            
            // Insertar la relación
            $stmt = $pdo->prepare("INSERT INTO lista_productos (lista_id, producto_id) VALUES (?, ?)");
            return $stmt->execute([$listaId, $productoId]);
        } catch (PDOException $e) {
            error_log("Error añadiendo producto a lista: " . $e->getMessage());
            return false;
        }
    }

    public static function getImagenesBase64PorProductoId($producto_id) {
        $db = self::connect(); // o ajusta si usas $this->db
        $stmt = $db->prepare("SELECT url FROM imagenes WHERE producto_id = ?");
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

    public static function shareList($listaId, $messageId, $sharedById, $isPublic = false) {
        $pdo = self::connect();
        $stmt = $pdo->prepare("
            INSERT INTO shared_lists (message_id, lista_id, shared_by, is_public) 
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$messageId, $listaId, $sharedById, $isPublic]);
    }

    public static function getSharedList($messageId) {
        $pdo = self::connect();
        $stmt = $pdo->prepare("
            SELECT sl.*, l.nombre as lista_nombre, u.username as shared_by_username
            FROM shared_lists sl
            JOIN listas l ON sl.lista_id = l.id
            JOIN User u ON sl.shared_by = u.id
            WHERE sl.message_id = ?
        ");
        $stmt->execute([$messageId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getListProducts($listaId) {
        $pdo = self::connect();
        $stmt = $pdo->prepare("
            SELECT p.* 
            FROM lista_productos lp
            JOIN productos p ON lp.producto_id = p.id
            WHERE lp.lista_id = ?
        ");
        $stmt->execute([$listaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
  }