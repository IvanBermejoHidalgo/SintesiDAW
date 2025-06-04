<?php

require_once __DIR__ . '/../services/MisListasService.php';

class MisListasController {
    private $service;

    public function __construct($db, $userId) {
        $this->service = new MisListasService($db, $userId);
    }

    public function getListas() {
        return $this->service->getListas();
    }

    public function getProductosPorLista($listaId) {
        return $this->service->getProductosPorLista($listaId);
    }

    public function eliminarProductoDeLista($lista_id, $producto_id) {
        return $this->service->eliminarProductoDeLista($lista_id, $producto_id);
    }

    public function eliminarLista($lista_id) {
        return $this->service->eliminarLista($lista_id);
    }

    public function crearLista($nombreLista) {
        return $this->service->crearLista($nombreLista);
    }

    public function procesarFormulario() {
        $this->service->procesarFormulario();
    }

    public function getSharedList($messageId) {
        return $this->service->getSharedList($messageId);
    }

    public function getListProducts($listaId) {
        return $this->service->getListProducts($listaId);
    }
}
