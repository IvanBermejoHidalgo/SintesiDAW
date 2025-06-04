<?php

require_once __DIR__ . '/../repositories/MisListasRepository.php';

class MisListasService {
    private $repository;

    public function __construct($db, $userId) {
        $this->repository = new MisListasRepository($db, $userId);
    }

    public function getListas() {
        return $this->repository->getListas();
    }

    public function getProductosPorLista($listaId) {
        return $this->repository->getProductosPorLista($listaId);
    }

    public function eliminarProductoDeLista($lista_id, $producto_id) {
        return $this->repository->eliminarProductoDeLista($lista_id, $producto_id);
    }

    public function eliminarLista($lista_id) {
        return $this->repository->eliminarLista($lista_id);
    }

    public function crearLista($nombreLista) {
        return $this->repository->crearLista($nombreLista);
    }

    public function procesarFormulario() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $producto_id = $_POST['producto_id'] ?? null;
        $lista_id = $_POST['lista_id'] ?? null;
        $nombre_nueva_lista = trim($_POST['nombre_nueva_lista'] ?? '');

        if (!$producto_id) {
            return;
        }

        // Crear nueva lista si hay nombre
        if (!empty($nombre_nueva_lista)) {
            $this->crearLista($nombre_nueva_lista);
            $lista_id = $this->repository->getUltimaListaId();
        }

        if (!$lista_id) {
            return;
        }

        // Agregar producto a la lista si no existe
        if (!$this->repository->existeProductoEnLista($lista_id, $producto_id)) {
            $this->repository->agregarProductoALista($lista_id, $producto_id);
        }

        // Redirigir de vuelta
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
}
