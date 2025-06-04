<?php

class TiendaService {
    private $repository;
    private $imagenService;

    public function __construct(TiendaRepository $repository, ImagenService $imagenService) {
        $this->repository = $repository;
        $this->imagenService = $imagenService;
    }

    public function obtenerDatosTienda($categoria, $userId, $buscar) {
        $productos = $this->repository->getProductosPorCategoria($categoria, $buscar);

        foreach ($productos as &$producto) {
            $producto['imagenes_base64'] = $this->imagenService->getImagenesBase64($producto['id']);
        }

        $carrito = $this->repository->getProductosCarrito($userId);
        foreach ($carrito as &$producto) {
            $producto['imagenes_base64'] = $this->imagenService->getImagenesBase64($producto['id']);
        }

        return [
            'productos' => $productos,
            'carrito' => $carrito
        ];
    }

    public function getProductoPorId($id) {
        $producto = $this->repository->getProductoPorId($id);
        if ($producto) {
            $producto['imagenes_base64'] = $this->imagenService->getImagenesBase64($producto['id']);
        }
        return $producto;
    }
}
