<?php
// Mostrar todos los errores (solo en desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once "../vendor/autoload.php";
require_once "../src/controller/SessionController.php";
require_once "../src/controller/DatabaseController.php"; 
require_once "../src/controller/HomeController.php"; 
require_once "../src/controller/ProfileController.php"; 
require_once "../src/controller/TiendaController.php"; 
require_once "../src/controller/CartController.php";
require_once "../src/controller/CheckoutController.php";
require_once "../src/controller/PedidoConfirmadoController.php";
require_once "../src/controller/MisListasController.php";
require_once "../src/controller/PedidosController.php";


session_start();

// Configuración de idioma y localización
$language = $_SESSION['language'] ?? 'es';
$locale = ($language === 'en') ? 'en_US.UTF-8' : 'es_ES.UTF-8';

putenv("LC_ALL=$locale");
setlocale(LC_ALL, $locale);
bindtextdomain('messages', '/var/www/sintesi.local/locale');
textdomain('messages');

// Configurar Twig
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/views');
$twig = new \Twig\Environment($loader, ['cache' => false]);
$twig->addFunction(new \Twig\TwigFunction('_', fn($string) => gettext($string)));

$twig->addFunction(new \Twig\TwigFunction('get_shared_list', function($messageId) {
    $db = DatabaseController::connect();
    $userId = $_SESSION['user_id'] ?? null;
    $misListasController = new MisListasController($db, $userId);
    return $misListasController->getSharedList($messageId);
}));

$twig->addFunction(new \Twig\TwigFunction('get_list_products', function($listaId) {
    $db = DatabaseController::connect();
    $userId = $_SESSION['user_id'] ?? null;
    $misListasController = new MisListasController($db, $userId);
    return $misListasController->getListProducts($listaId);
}));

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = explode('/', trim($requestUri, '/'));
$views = __DIR__ . '/views/';

// Redirigir a admin.php si es ruta admin
if (isset($path[0]) && $path[0] === 'admin') {
    require __DIR__ . '/admin.php';
    exit();
}

// Rutas para incrementar o decrementar en carrito
if (
    isset($path[0], $path[1], $path[2]) &&
    $path[0] === 'carrito' &&
    in_array($path[1], ['incrementar', 'decrementar']) &&
    is_numeric($path[2])
) {
    $cartController = new CartController();
    $productId = intval($path[2]);
    $talla = $_POST['talla'] ?? '';

    if ($path[1] === 'incrementar') {
        $cartController->incrementar($productId, $talla);
    } else {
        $cartController->decrementar($productId, $talla);
    }
    exit();
}

// Manejo de rutas principales
$route = $path[0] ?? '';

// Mostrar formulario de restablecimiento de contraseña fuera del switch
// if ($route === 'reset_form') {
//     try {
//         echo $twig->render('reset_password/reset_form.html', ['token' => $_GET['token'] ?? '']);
//     } catch (\Exception $e) {
//         echo "<h2>Error al cargar la plantilla:</h2>";
//         echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
//     }
//     exit;
// }

switch ($route) {
    case '':
    case '/':
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $result = SessionController::userLogin($username, $password);
            if ($result === "success") {
                header("Location: /home");
                exit();
            } else {
                echo "<script>alert('$result');</script>";
            }
        }
        require $views . 'login.php';
        break;

    case 'signup':
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $gender = $_POST['gender'] ?? '';
            $result = SessionController::userSignUp($username, $email, $password, $gender);
            if ($result === "Usuario registrado exitosamente") {
                header("Location: /");
                exit();
            } else {
                echo "<script>alert('$result');</script>";
            }
        }
        require $views . 'signup.php';
        break;

    case 'home':
        if (!isset($_SESSION['user_id'])) {
            header("Location: /");
            exit();
        }

        
        $homeController = new HomeController();
        $homeData = $homeController->handleRequest();
        $misListasController = new MisListasController(DatabaseController::connect(), $_SESSION['user_id']);
        $listasUsuario = $misListasController->getListas();
        echo $twig->render('home.html', [
            'messages' => $homeData['messages'],
            'userData' => $homeController->getUserById($_SESSION['user_id']),
            'current_user_id' => $_SESSION['user_id'],
            'language' => $language,
            'current_page' => 'home',
            'listas_usuario' => $listasUsuario
        ]);
        break;

    case 'profile':
        if (!isset($_SESSION['user_id'])) {
            header("Location: /");
            exit();
        }
        $profileController = new ProfileController();
        $profileData = $profileController->handleRequest();
        echo $twig->render('profile.html', [
            'profileUser' => $profileData['profileUser'],
            'userMessages' => $profileData['userMessages'],
            'current_user_id' => $_SESSION['user_id'],
            'language' => $language,
            'current_page' => 'profile'
        ]);
        break;

    case 'aboutus':
        echo $twig->render('aboutus.html', [
            'language' => $language,
            'current_user_id' => $_SESSION['user_id'],
            'userData' => DatabaseController::getUserById($_SESSION['user_id']),
            'language' => $language,
            'current_page' => 'aboutus'
        ]);
        break;

    case 'carrito':
        $cartController = new CartController();
        $data = $cartController->handleRequest();
        echo $twig->render('tienda/carrito.html', [
            'cartItems' => $data['cartItems'],
            'userData' => DatabaseController::getUserById($_SESSION['user_id']),
            'language' => $language,
            'current_page' => 'carrito'
        ]);
        break;

    case 'producto':
        if (isset($_SESSION['user_id'], $path[1])) {
            $productId = $path[1];
            $userId = $_SESSION['user_id'];

            $producto = (new TiendaController())->getProductoPorId($productId);
            
            $misListasController = new MisListasController(DatabaseController::connect(), $userId);
            $listas = $misListasController->getListas();
            
            echo $twig->render('tienda/producto.html', [
                'producto' => $producto,
                'userData' => DatabaseController::getUserById($_SESSION['user_id']),
                'language' => $language,
                'listas_usuario' => $listas,
                'current_page' => 'producto'
            ]);
        } else {
            header("Location: /tienda");
            exit();
        }
        break;

    case 'checkout':
        if (!isset($_SESSION['user_id'])) {
            header("Location: /");
            exit();
        }
        $checkoutController = new CheckoutController($twig);
        $checkoutController->handleRequest();
        break;

    case 'pedido_confirmado':
        if (!isset($_SESSION['user_id'])) {
            header("Location: /");
            exit();
        }
        $pedidoConfirmadoController = new PedidoConfirmadoController($twig);
        $pedidoConfirmadoController->handleRequest();
        break;

    case 'tienda':
        if (!isset($_SESSION['user_id'])) {
            header("Location: /");
            exit();
        }
        $tiendaController = new TiendaController();
        $tiendaData = $tiendaController->handleRequest();
        echo $twig->render('tienda/tienda.html', [
            'productos' => $tiendaData['productos'],
            'categoria_actual' => $tiendaData['categoria_actual'],
            'userData' => DatabaseController::getUserById($_SESSION['user_id']),
            'current_page' => 'tienda/' . $tiendaData['categoria_actual'],
            'language' => $language
        ]);
        break;

    case 'mis_pedidos':
        if (!isset($_SESSION['user_id'])) {
            header("Location: /");
            exit();
        }
        $userId = $_SESSION['user_id'];
        $db = DatabaseController::connect();

        $stmt = $db->prepare("
            SELECT p.id, p.fecha, p.metodo_pago, p.nombre, p.email, p.telefono, p.direccion, p.ciudad, p.codigo_postal, p.pais,
                COALESCE(SUM(pd.cantidad * pd.precio), 0) AS total_amount
            FROM pedidos p
            LEFT JOIN pedido_detalles pd ON pd.pedido_id = p.id
            WHERE p.usuario_id = ?
            GROUP BY p.id
            ORDER BY p.fecha DESC
        ");
        $stmt->execute([$userId]);
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $homeController = new HomeController();
        foreach ($pedidos as &$pedido) {
            $stmtItems = $db->prepare("
                SELECT pr.name AS name, pd.talla, pd.cantidad, pd.precio
                FROM pedido_detalles pd
                JOIN productos pr ON pr.id = pd.producto_id
                WHERE pd.pedido_id = ?
            ");
            $stmtItems->execute([$pedido['id']]);
            $pedido['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
        }
        unset($pedido);

        echo $twig->render('tienda/mis_pedidos.html', [
            'pedidos' => $pedidos,
            'userData' => $homeController->getUserById($userId),
            'language' => $language,
            'current_page' => 'mis_pedidos'
        ]);
        break;


    case 'mis_listas':
        if (!isset($_SESSION['user_id'])) {
            header("Location: /");
            exit();
        }

        $db = DatabaseController::connect();
        $userId = $_SESSION['user_id'];
        $controller = new MisListasController($db, $userId);

        // Procesar solicitudes AJAX
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $uri = $_SERVER['REQUEST_URI'];
            
            // Verificar si es una solicitud AJAX
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            
            if (strpos($uri, '/mis_listas/eliminar_producto') !== false) {
                $response = $controller->eliminarProductoDeLista($_POST['lista_id'], $_POST['producto_id']);
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }

            if (strpos($uri, '/mis_listas/eliminar_lista') !== false) {
                $response = $controller->eliminarLista($_POST['lista_id']);
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }

            if (strpos($uri, '/mis_listas/crear') !== false) {
                $response = $controller->crearLista($_POST['nombre_lista']);
                
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                } else {
                    // Redirección para solicitudes no AJAX (fallback)
                    header("Location: /mis_listas");
                    exit;
                }
            }

            // Crear nueva lista o añadir producto (fallback para navegadores sin JS)
            $controller->procesarFormulario();
        }

        // Obtener listas y productos para renderizar la página
        $listas = $controller->getListas();
        foreach ($listas as &$lista) {
            $lista['productos'] = $controller->getProductosPorLista($lista['id']);
        }

        echo $twig->render('tienda/mis_listas.html', [
            'listas' => $listas,
            'userData' => DatabaseController::getUserById($userId),
            'language' => $language,
            'current_page' => 'mis_listas'
        ]);
        break;

    case 'user':
        if (!isset($_SESSION['user_id'])) {
            header("Location: /");
            exit();
        }
        $userId = $path[1] ?? $_GET['id'] ?? null;
        if (!$userId) {
            header("Location: /home");
            exit();
        }
        $profileController = new ProfileController();
        $profileData = $profileController->handlePublicProfileRequest($userId);
        $currentUserData = ProfileController::getUserById($_SESSION['user_id']);
        echo $twig->render('user.html', [
            'profileUser' => $profileData['profileUser'],
            'userMessages' => $profileData['userMessages'],
            'current_user_id' => $_SESSION['user_id'],
            'userData' => $currentUserData,
            'language' => $language,
            'current_page' => 'profile'
        ]);
        break;

    case 'logout':
        session_destroy();
        header("Location: /");
        exit();
        break;

    case 'change-language':
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $language = $_POST['language'] ?? 'es';
            $_SESSION['language'] = $language;
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
            exit();
        }
        break;

    case 'forgot_password':
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $email = $_POST['email'] ?? '';
            $result = SessionController::sendResetPasswordEmail($email);
            echo $twig->render('reset_password/forgot_password.php', ['message' => $result]);
        } else {
            echo $twig->render('reset_password/forgot_password.php');
        }
        break;

    case 'reset_form':
        error_log("Accediendo a reset_form. Método: " . $_SERVER['REQUEST_METHOD']);
        $token = $_GET['token'] ?? '';
        error_log("Token recibido: $token");

        try {
            // Verificar que el token existe en la base de datos
            $db = DatabaseController::connect();
            
            $stmt = $db->prepare("SELECT pr.user_id, pr.expires_at, u.username, u.email 
                                FROM password_resets pr
                                JOIN User u ON pr.user_id = u.id
                                WHERE pr.token = ?");
            if (!$stmt->execute([$token])) {
                throw new Exception("Error en consulta SQL: " . implode(", ", $stmt->errorInfo()));
            }
            
            $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$tokenData) {
                throw new Exception("Token no encontrado en la base de datos");
            }

            error_log("Token válido encontrado para usuario: " . $tokenData['username']);

            if (strtotime($tokenData['expires_at']) < time()) {
                throw new Exception("Token expirado: " . $tokenData['expires_at']);
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                error_log("Procesando solicitud POST para restablecimiento");
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                
                // Validaciones
                if (empty($newPassword)) {
                    throw new Exception("La contraseña no puede estar vacía");
                }

                if ($newPassword !== $confirmPassword) {
                    throw new Exception("Las contraseñas no coinciden");
                }

                // Procesar el cambio de contraseña
                $result = SessionController::resetPasswordWithToken($token, $newPassword);
                error_log("Resultado de resetPasswordWithToken: $result");
                
                if ($result === "Contraseña restablecida correctamente") {
                    echo $twig->render('reset_password/reset_success.html');
                    exit();
                }
                throw new Exception($result);
            }

            // Mostrar formulario de restablecimiento
            echo $twig->render('reset_password/reset_form.html', [
                'token' => $token,
                'error' => null
            ]);
            
        } catch (Exception $e) {
            error_log("Error en reset_form: " . $e->getMessage());
            echo $twig->render('reset_password/invalid_token.html', [
                'message' => $e->getMessage()
            ]);
        }
        break;
    case 'reset_password':
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $token = $_POST['token'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($newPassword)) {
                echo $twig->render('reset_password/reset_form.html', [
                    'token' => $token,
                    'error' => 'La contraseña no puede estar vacía'
                ]);
                exit;
            }

            if ($newPassword !== $confirmPassword) {
                echo $twig->render('reset_password/reset_form.html', [
                    'token' => $token,
                    'error' => 'Las contraseñas no coinciden'
                ]);
                exit;
            }

            $result = SessionController::resetPasswordWithToken($token, $newPassword);

            if ($result === "Contraseña restablecida correctamente") {
                header("Location: /login");
                exit;
            } else {
                echo $twig->render('reset_password/reset_form.html', [
                    'token' => $token,
                    'error' => $result
                ]);
                exit;
            }
        }
        break;

    default:
        require $views . '404.php';
        break;
}
