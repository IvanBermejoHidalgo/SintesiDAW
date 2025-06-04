<?php

require_once __DIR__ . '/../services/TiendaService.php';
require_once __DIR__ . '/../services/ImagenService.php';
require_once __DIR__ . '/../repositories/TiendaRepository.php';
require_once __DIR__ . '/MisListasController.php';

class TiendaController {
    private $service;

    public function __construct() {
        $repository = new TiendaRepository();
        $imagenService = new ImagenService();
        $this->service = new TiendaService($repository, $imagenService);
    }

    public function handleRequest() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /");
            exit();
        }

        $categoria = $this->getCategoriaDesdeRuta();
        $userId = $_SESSION['user_id'];
        $buscar = $_GET['buscar'] ?? '';

        $misListasController = new MisListasController(DatabaseController::connect(), $userId);
        $listas = $misListasController->getListas();

        $data = $this->service->obtenerDatosTienda($categoria, $userId, $buscar);

        return [
            'productos' => $data['productos'],
            'categoria_actual' => $categoria,
            'buscar' => $buscar,
            'userData' => SessionController::getUserData($userId),
            'carrito' => $data['carrito'],
            'listas' => $listas,
            'current_page' => 'tienda/' . $categoria
        ];
    }

    private function getCategoriaDesdeRuta() {
        $uri = $_SERVER['REQUEST_URI'];
        if (preg_match('#^/tienda/(hombre|mujer|todos)$#', $uri, $matches)) {
            return $matches[1];
        }
        return 'todos';
    }

    public function getProductoPorId($id) {
        return $this->service->getProductoPorId($id);
    }
}
