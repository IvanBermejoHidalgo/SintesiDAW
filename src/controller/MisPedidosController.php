<?php

class MisPedidosController {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function handleRequest() {
        session_start();
        if (!isset($_SESSION['usuario_id'])) {
            // Redirigir o mostrar error
            header('Location: login.php');
            exit;
        }

        $usuario_id = $_SESSION['usuario_id'];

        // Preparar y ejecutar consulta
        $stmt = $this->db->prepare("
            SELECT id, fecha, nombre
            FROM pedidos
            WHERE usuario_id = ?
            ORDER BY fecha DESC
        ");
        $stmt->execute([$usuario_id]);
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Mostrar resultados (ejemplo simple)
        foreach ($pedidos as $pedido) {
            echo "Pedido #{$pedido['id']} - Fecha: {$pedido['fecha']} - Nombre: {$pedido['nombre']}<br>";
        }
    }
}

