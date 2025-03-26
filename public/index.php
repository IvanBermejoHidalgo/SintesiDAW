<?php
// Notificar todos los errores de PHP
// Cambiar a 0 en producción
error_reporting(-1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once "../vendor/autoload.php";
require_once "../src/controller/SessionController.php";
require_once "../src/controller/DatabaseController.php"; // Asegúrate de incluir DatabaseController
session_start();


$language = $_SESSION['language'] ?? 'es'; // Idioma por defecto: español

// Configura el locale según el idioma seleccionado
if ($language === 'es') {
    $locale = 'es_ES.UTF-8'; // Locale para español
} elseif ($language === 'en') {
    $locale = 'en_US.UTF-8'; // Locale para inglés
}

putenv("LC_ALL=$locale");
setlocale(LC_ALL, $locale);

// Usa una ruta absoluta para evitar problemas con rutas relativas
bindtextdomain('messages', '/var/www/sintesi.local/locale');
textdomain('messages');

// Verifica la ruta corregida
// echo "Idioma seleccionado: " . $language . "<br>";
// echo "Locale configurado: " . $locale . "<br>";
// echo "Locale actual: " . setlocale(LC_ALL, 0) . "<br>";
// echo "Ruta de traducciones: " . realpath('/var/www/webscraping.local/locale') . "<br>";
// echo "Archivo de traducción (es): " . realpath('/var/www/webscraping.local/locale/es/LC_MESSAGES/messages.mo') . "<br>";
// echo "Archivo de traducción (en): " . realpath('/var/www/webscraping.local/locale/en/LC_MESSAGES/messages.mo') . "<br>";

$loader = new \Twig\Loader\FilesystemLoader('views');
$twig = new \Twig\Environment($loader, [
    'cache' => false,
]);


// Añadir la función _() a Twig para traducciones
$twig->addFunction(new \Twig\TwigFunction('_', function ($string) {
    return gettext($string);
}));

$path = explode('/', trim($_SERVER['REQUEST_URI']));
$views = __DIR__ . '/views/';

// Si la ruta comienza con /admin, redirige a admin.php
if ($path[1] === 'admin') {
    require __DIR__ . '/admin.php';
    exit();
}

// Manejo de rutas principales
switch ($path[1]) {
    case '':
    case '/':

        
        require $views . 'login.php';
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $result = SessionController::userLogin($username, $password);
            if ($result === "success") {
                header("Location: /home");
                exit();
            } else {
                echo "<script>alert('$result');</script>";
            }
        }
        break;

    case 'signup':

        require $views . 'signup.php';
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $result = SessionController::userSignUp($username, $email, $password);
            if ($result === "Usuario registrado exitosamente") {
                header("Location: /");
                exit();
            } else {
                echo "<script>alert('$result');</script>";
            }
        }
        break;

    case 'home':
        if (isset($_SESSION['user_id'])) {
            $db = DatabaseController::connect();
            
            // Manejar envío de mensajes
            if ($_SERVER["REQUEST_METHOD"] === "POST") {
                if (isset($_POST['like_message'])) {
                    DatabaseController::addLike($_SESSION['user_id'], $_POST['like_message']);
                    header("Location: /home");
                    exit();
                } elseif (isset($_POST['unlike_message'])) {
                    DatabaseController::removeLike($_SESSION['user_id'], $_POST['unlike_message']);
                    header("Location: /home");
                    exit();
                } elseif (isset($_POST['delete_message'])) {
                    DatabaseController::deleteMessage($_SESSION['user_id'], $_POST['delete_message']);
                    header("Location: /home");
                    exit();
                } elseif (isset($_POST['content'])) {
                    // Manejar envío de mensajes (existente)
                    $content = htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8');
                    $stmt = $db->prepare(
                        "INSERT INTO messages (user_id, content, created_at) VALUES (?, ?, NOW())"
                    );
                    $stmt->execute([$_SESSION['user_id'], $content]);
                    header("Location: /home");
                    exit();
                }
            }
    
            // Obtener mensajes
            $stmt = $db->query("
                SELECT m.*, u.username 
                FROM messages m
                JOIN User u ON m.user_id = u.id
                ORDER BY m.created_at DESC
                LIMIT 50
            ");
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            $userData = SessionController::getUserData($_SESSION['user_id']);
    
            echo $twig->render('home.html', [
                'messages' => $messages,
                'userData' => $userData,
                'language' => $language,
                'current_page' => 'home' // Para resaltar la página activa
            ]);
        } else {
            header("Location: /");
            exit();
        }
        break;

    case 'logout':
        // Cerrar sesión
        session_start(); // Iniciar la sesión si no está iniciada
        session_destroy(); // Destruir la sesión
        header("Location: /"); // Redirigir al login
        exit();
        break;

    case 'change-language':
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $language = $_POST['language'];
            $_SESSION['language'] = $language; // Guardar el idioma en la sesión

            // Configurar el locale con el nuevo idioma
            if ($language === 'es') {
                $locale = 'es_ES.UTF-8';
            } elseif ($language === 'en') {
                $locale = 'en_US.UTF-8';
            }

            putenv("LC_ALL=$locale");
            setlocale(LC_ALL, $locale);

            // Redirigir a la página anterior
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        }
        break;

    case 'not-found':
    default:
        http_response_code(404);
        require $views . '404.php';
        break;
}