<?php
// Verificar si la sesión no está ya iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../vendor/autoload.php";
require_once "../src/controller/AdminController.php";
require_once "../src/controller/DatabaseController.php";

// Configurar Twig para buscar plantillas en múltiples directorios
$loader = new \Twig\Loader\FilesystemLoader('views/admin');
$twig = new \Twig\Environment($loader, ['cache' => false]);

$path = explode('/', trim($_SERVER['REQUEST_URI'], '/')); // Eliminar '/' inicial y final
$views = __DIR__ . '/views/admin/';

// Verificar si el administrador está logueado
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Rutas: /admin, /admin/dashboard, /admin/logout, /admin/crear-producto, /admin/editar-producto/{id}, /admin/eliminar-producto/{id}
if (isset($path[0]) && $path[0] === 'admin') {
    $action = isset($path[2]) ? $path[1] . '/' . $path[2] : ($path[1] ?? '');

    if ($action === 'dashboard') {
        if (isAdminLoggedIn()) {
            // Obtener datos para el dashboard
            $userCount = DatabaseController::getUserCount();
            $messageCount = DatabaseController::getMessageCount();
            $activeUsers = DatabaseController::getActiveUsers();
            $productCount = DatabaseController::getProductCount();
            $totalCompras = DatabaseController::obtenerTotalCompras();
            $totalCantidadCompras = DatabaseController::obtenerCantidadCompras();
            $totalListas = DatabaseController::getTotalListas();

            echo $twig->render('dashboard.php', [
                'userCount' => $userCount,
                'messageCount' => $messageCount,
                'activeUsers' => $activeUsers,
                'productCount' => $productCount,
                'totalCompras' => $totalCompras,
                'totalCantidadCompras' => $totalCantidadCompras,
                'totalListas' => $totalListas,
            ]);
        } else {
            header("Location: /admin");
            exit();
        }

    } elseif ($action === 'users-by-gender') {
        if (isAdminLoggedIn()) {
            $genderStats = DatabaseController::getUsersByGender();
            echo $twig->render('users_by_gender.php', [
                'genderStats' => $genderStats
            ]);
        } else {
            header("Location: /admin");
            exit();
        }

    } elseif ($action === 'products-by-category') {
        if (isAdminLoggedIn()) {
            $genderStats = DatabaseController::getProductsByCategory();
            echo $twig->render('products_by_category.php', [
                'hombreCount' => $genderStats['hombre'] ?? 0,
                'mujerCount' => $genderStats['mujer'] ?? 0,
                'todosCount' => $genderStats['todos'] ?? 0
            ]);
        } else {
            header("Location: /admin");
            exit();
        }
        
    } elseif ($action === 'logout') {
        session_destroy();
        header("Location: /admin");
        exit();

    } elseif ($action === 'editar-productos') {
        if (isAdminLoggedIn()) {
            $productos = DatabaseController::getAllProductos();

            // Para cada producto, añadimos la URL de la imagen
            foreach ($productos as &$producto) {
                $producto['imagenes_base64'] = DatabaseController::getImagenesBase64PorProductoId($producto['id']);
            }
            unset($producto);

            echo $twig->render('editar_productos.php', [
                'productos' => $productos
            ]);
        } else {
            header("Location: /admin");
            exit();
        }
    } elseif ($action === 'crear-producto') {
        if (!isAdminLoggedIn()) {
            header("Location: /admin");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'price' => $_POST['price'] ?? 0,
                'category' => $_POST['category'] ?? '',
            ];

            $files = $_FILES;

            $success = DatabaseController::insertarProducto($data, $files);
            if ($success) {
                header("Location: /admin/editar-productos");
                exit();
            } else {
                $error = "Error al crear el producto";
            }
        }

        echo $twig->render('crear_producto.php', [
            'error' => $error ?? null,
            'data' => $_POST ?? []
        ]);

    } elseif (preg_match('#^editar-producto/(\d+)$#', $action, $matches)) {
        if (!isAdminLoggedIn()) {
            header("Location: /admin");
            exit();
        }
        $id = intval($matches[1]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'price' => $_POST['price'] ?? 0,
                'category' => $_POST['category'] ?? '',
                'image_path' => $_POST['image_path'] ?? null,
            ];
            $success = DatabaseController::actualizarProducto($id, $data);
            if ($success) {
                header("Location: /admin/editar-productos");
                exit();
            } else {
                $error = "Error al actualizar el producto";
            }
        } else {
            $data = DatabaseController::getProductoById($id);
            if (!$data) {
                http_response_code(404);
                echo $twig->render('404.php');
                exit();
            }
        }
        echo $twig->render('editar_producto.php', [
            'error' => $error ?? null,
            'data' => $data
        ]);

    } elseif (preg_match('#^eliminar-producto/(\d+)$#', $action, $matches)) {
        if (!isAdminLoggedIn()) {
            header("Location: /admin");
            exit();
        }
        $id = intval($matches[1]);
        DatabaseController::eliminarProducto($id);
        header("Location: /admin/editar-productos");
        exit();

    } else {
        // Página login admin
        require $views . 'login.php';

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $result = AdminController::adminLogin($username, $password);

            if ($result === "success") {
                header("Location: /admin/dashboard");
                exit();
            } else {
                echo "<script>alert('$result');</script>";
            }
        }
    }
} else {
    http_response_code(404);
    require __DIR__ . '/views/404.php';
}
