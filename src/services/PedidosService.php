<?php

class PedidosService {
    private $repository;

    public function __construct(PedidosRepository $repository) {
        $this->repository = $repository;
    }

    public function getPedidosUsuario($userId) {
        $pedidos = $this->repository->fetchPedidos($userId);

        foreach ($pedidos as &$pedido) {
            $pedido['items'] = $this->repository->fetchDetallesPedido($pedido['id']);

            // Calcular total
            $total = 0;
            foreach ($pedido['items'] as $item) {
                $total += $item['precio'] * $item['cantidad'];
            }

            $pedido['total_amount'] = $total;
        }

        return $pedidos;
    }
}
