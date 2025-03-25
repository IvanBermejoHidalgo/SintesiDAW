<?php
session_start(); // Inicia la sesión al principio

require_once "../vendor/autoload.php";
require_once "../src/controller/AdminController.php";
require_once "../src/controller/TeamsController.php";
require_once "../src/controller/DriversController.php";
require_once "../src/controller/RacesController.php";
require_once "../src/controller/DatabaseController.php";

$loader = new \Twig\Loader\FilesystemLoader('views/admin');
$twig = new \Twig\Environment($loader, ['cache' => false]);

$path = explode('/', trim($_SERVER['REQUEST_URI']));
$views = __DIR__ . '/views/admin/';

// Verificar si el administrador está logueado
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Manejo de rutas
switch ($path[1]) {
    case 'admin':
        if ($path[2] === 'dashboard') {
            if (isAdminLoggedIn()) {
                // Obtener datos para el dashboard
                $teams = DatabaseController::getTeams();
                $drivers = DatabaseController::getDrivers();
                $races = DatabaseController::getRaces();

                // Renderizar el dashboard con los datos
                echo $twig->render('dashboard.php', [
                    'teams' => $teams,
                    'drivers' => $drivers,
                    'races' => $races,
                ]);
            } else {
                // Redirigir al login si no está autenticado
                header("Location: /admin");
                exit();
            }






        } elseif ($path[2] === 'scuderias') {
            if (isAdminLoggedIn()) {
                if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($path[3])) {
                    // Procesar añadir o editar equipo
                    $team_name = $_POST['team_name'];
                    //$driver1 = $_POST['driver1'];
                    //$driver2 = $_POST['driver2'];
                    $img_team = $_POST['img_team'];
                    $img_car = $_POST['img_car'];

                    if ($path[3] === 'add') {
                        //TeamsController::addTeam($team_name, $driver1, $driver2, $img_team, $img_car);
                        TeamsController::addTeam($team_name, $img_team, $img_car);
                        header("Location: /admin/scuderias");
                        exit();
                    } elseif ($path[3] === 'edit' && isset($path[4])) {
                        $id = $path[4];
                        //TeamsController::editTeam($id, $team_name, $driver1, $driver2, $img_team, $img_car);
                        TeamsController::editTeam($id, $team_name, $img_team, $img_car);
                        header("Location: /admin/scuderias");
                        exit();
                    }
                } elseif ($path[3] === 'delete' && isset($path[4])) {
                    // Eliminar equipo
                    $id = $path[4];
                    TeamsController::deleteTeam($id);
                    header("Location: /admin/scuderias");
                    exit();
                } else {
                    // Mostrar lista de equipos
                    $teams = DatabaseController::getTeams();
                    $teamsWithDrivers = [];

                    foreach ($teams as $team) {
                        $drivers = TeamsController::getDriversByTeam($team['id']); // Usar team_id en lugar de team_name
                        $team['drivers'] = $drivers; // Añadir los pilotos al equipo
                        $teamsWithDrivers[] = $team;
                    }

                    echo $twig->render('scuderias/teams.php', ['teams' => $teamsWithDrivers]);
                }
            } else {
                header("Location: /admin");
                exit();
            }





        } elseif ($path[2] === 'add-team') {
            if (isAdminLoggedIn()) {
                echo $twig->render('scuderias/add_team.php');
            } else {
                header("Location: /admin");
                exit();
            }




        } elseif ($path[2] === 'edit-team' && isset($path[3])) {
            if (isAdminLoggedIn()) {
                $id = $path[3];
                $team = TeamsController::getTeamById($id);
                echo $twig->render('scuderias/edit_team.php', ['team' => $team]);
            } else {
                header("Location: /admin");
                exit();
            }





        } elseif ($path[2] === 'drivers') {
            if (isAdminLoggedIn()) {
                if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($path[3])) {
                    // Procesar añadir o editar piloto
                    $first_name = $_POST['first_name'];
                    $last_name = $_POST['last_name'];
                    $team_id = $_POST['team_id'];
                    $country = $_POST['country'];
                    $flag_url = $_POST['flag_url'];
                    $piloto_img = $_POST['piloto_img'];
    
                    if ($path[3] === 'add') {
                        DriversController::addDriver($first_name, $last_name, $team_id, $country, $flag_url, $piloto_img);
                        header("Location: /admin/drivers");
                        exit();
                    } elseif ($path[3] === 'edit' && isset($path[4])) {
                        $id = $path[4];
                        DriversController::editDriver($id, $first_name, $last_name, $team_id, $country, $flag_url, $piloto_img);
                        header("Location: /admin/drivers");
                        exit();
                    }
                } elseif ($path[3] === 'delete' && isset($path[4])) {
                    // Eliminar piloto
                    $id = $path[4];
                    DriversController::deleteDriver($id);
                    header("Location: /admin/drivers");
                    exit();
                } else {
                    // Mostrar lista de pilotos
                    $drivers = DatabaseController::getDrivers();
                    echo $twig->render('drivers/drivers.php', ['drivers' => $drivers]);
                }
            } else {
                header("Location: /admin");
                exit();
            }
        } elseif ($path[2] === 'add-driver') {
            if (isAdminLoggedIn()) {
                $teams = DatabaseController::getTeams();
                echo $twig->render('drivers/add_driver.php', ['teams' => $teams]);
                //echo $twig->render('drivers/add_driver.php');
            } else {
                header("Location: /admin");
                exit();
            }
            break;
        } elseif ($path[2] === 'edit-driver' && isset($path[3])) {
            if (isAdminLoggedIn()) {
                $id = $path[3];
                $driver = DriversController::getDriverById($id);
                $teams = DatabaseController::getTeams(); // Obtener la lista de equipos
                echo $twig->render('drivers/edit_driver.php', [
                    'driver' => $driver,
                    'teams' => $teams // Pasar la lista de equipos
                ]);
            } else {
                header("Location: /admin");
                exit();
            }
            break;
        } elseif ($path[2] === 'races') {
            if (isAdminLoggedIn()) {
                if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($path[3])) {
                    // Procesar añadir o editar carrera
                    $grand_prix = $_POST['grand_prix'];
                    $race_date = $_POST['race_date'];
                    $winner = $_POST['winner'];
                    $team_id = $_POST['team_id']; // Asegúrate de que esto sea el ID del equipo
                    $laps = $_POST['laps'];
    
                    if ($path[3] === 'add') {
                        RacesController::addRace($grand_prix, $race_date, $winner, $team_id, $laps);
                        header("Location: /admin/races");
                        exit();
                    } elseif ($path[3] === 'edit' && isset($path[4])) {
                        $id = $path[4];
                        RacesController::editRace($id, $grand_prix, $race_date, $winner, $team_id, $laps);
                        header("Location: /admin/races");
                        exit();
                    }
                } elseif ($path[3] === 'delete' && isset($path[4])) {
                    // Eliminar carrera
                    $id = $path[4];
                    RacesController::deleteRace($id);
                    header("Location: /admin/races");
                    exit();
                } else {
                    // Mostrar lista de carreras
                    $races = DatabaseController::getRaces();
                    echo $twig->render('races/races.php', ['races' => $races]);
                }
            } else {
                header("Location: /admin");
                exit();
            }
        } elseif ($path[2] === 'add-race') {
            if (isAdminLoggedIn()) {
                $teams = DatabaseController::getTeams(); // Obtener la lista de equipos
                $drivers = DatabaseController::getDrivers(); // Obtener la lista de pilotos
                echo $twig->render('races/add_race.php', [
                    'teams' => $teams,
                    'drivers' => $drivers // Pasar la lista de pilotos
                ]);
            } else {
                header("Location: /admin");
                exit();
            }
        } elseif ($path[2] === 'edit-race' && isset($path[3])) {
            if (isAdminLoggedIn()) {
                $id = $path[3];
                $race = RacesController::getRaceById($id);
                $teams = DatabaseController::getTeams(); // Obtener la lista de equipos
                $drivers = DatabaseController::getDrivers(); // Obtener la lista de pilotos
                echo $twig->render('races/edit_race.php', [
                    'race' => $race,
                    'teams' => $teams, // Pasar la lista de equipos
                    'drivers' => $drivers // Pasar la lista de pilotos
                ]);
            } else {
                header("Location: /admin");
                exit();
            }
        } elseif ($path[2] === 'logout') {
            // Cerrar sesión
            session_start(); // Iniciar la sesión si no está iniciada
            session_destroy(); // Destruir la sesión
            header("Location: /admin"); // Redirigir al login
            exit();
        } else {
            // Mostrar el formulario de login para administradores
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
        break;

    default:
        http_response_code(404);
        require $views . '../404.php';
        break;
}