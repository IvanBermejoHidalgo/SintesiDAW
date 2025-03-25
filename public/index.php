<?php
// Notificar todos los errores de PHP
// Cambiar a 0 en producción
error_reporting(-1);

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
            // Obtener los datos de los ganadores individuales
            $winners = DatabaseController::getWinners();
            // Obtener los datos de los ganadores por equipos
            $teamWinners = DatabaseController::getTeamWinners();
    
            // Convertir los datos a un formato que Twig pueda usar
            $labels = [];
            $data = [];
            foreach ($winners as $winner) {
                $labels[] = $winner['winner'];
                $data[] = $winner['count'];
            }
    
            $teamLabels = [];
            $teamData = [];
            foreach ($teamWinners as $teamWinner) {
                $teamLabels[] = $teamWinner['team_name'];
                $teamData[] = $teamWinner['count'];
            }
    
            // Renderizar la plantilla Twig con los datos
            echo $twig->render('home.php', [
                'labels' => $labels,
                'data' => $data,
                'labels_js' => json_encode($labels),
                'data_js' => json_encode($data),
                'teamLabels' => $teamLabels,
                'teamData' => $teamData,
                'teamLabels_js' => json_encode($teamLabels),
                'teamData_js' => json_encode($teamData),
                'language' => $language, // Pasar el idioma a la plantilla
            ]);
        } else {
            header("Location: /");
            exit();
        }
        break;

    case 'scuderias':
        if (isset($_SESSION['user_id'])) {
            $teams = DatabaseController::getTeams();
            $teamsWithDrivers = DatabaseController::getTeamsWithDrivers();
            echo $twig->render('scuderias.html', [
                'teams' => $teams,
                'teamsWithDrivers' => $teamsWithDrivers,
                'language' => $language
            ]);
        } else {
            header("Location: /");
            exit();
        }
        break;

    case 'pilotos':
        if (isset($_SESSION['user_id'])) {
            $driver = DatabaseController::getDrivers();
            echo $twig->render('pilotos.html', [
                'drivers' => $driver,
                'language' => $language
            ]);
        } else {
            header("Location: /");
            exit();
        }
        break;

    case 'circuitos':
        if (isset($_SESSION['user_id'])) {
            $race = DatabaseController::getRaces();
            echo $twig->render('circuitos.html', [
                'races' => $race,
                'language' => $language
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