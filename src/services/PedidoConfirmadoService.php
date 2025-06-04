<?php

class PedidoConfirmadoService {
    private $repository;

    public function __construct(PedidoRepository $repository) {
        $this->repository = $repository;
    }

    public function getPedido($pedidoId, $userId) {
        return $this->repository->getPedidoByIdAndUser($pedidoId, $userId);
    }

    public function getDetallesPedido($pedidoId) {
        return $this->repository->getDetallesPedido($pedidoId);
    }
}
