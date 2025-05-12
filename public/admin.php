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

// Rutas: /admin, /admin/dashboard, /admin/logout
if (isset($path[0]) && $path[0] === 'admin') {
    $action = $path[1] ?? null;

    if ($action === 'dashboard') {
        if (isAdminLoggedIn()) {
            // Obtener datos para el dashboard
            $userCount = DatabaseController::getUserCount();
            $messageCount = DatabaseController::getMessageCount();
            $activeUsers = DatabaseController::getActiveUsers();
            $productCount = DatabaseController::getProductCount();

            echo $twig->render('dashboard.php', [
                'userCount' => $userCount,
                'messageCount' => $messageCount,
                'activeUsers' => $activeUsers,
                'productCount' => $productCount
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

    } else {
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