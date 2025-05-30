<?php
require_once '../src/controller/DatabaseController.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit('ID de producto no especificado');
}

$id = intval($_GET['id']);
$imagen = DatabaseController::getImagenPorProductoId($id);

if ($imagen) {
    // Si sabes el tipo exacto (por ejemplo, jpeg, png, etc.), cámbialo
    header("Content-Type: image/jpeg"); 
    header("Content-Length: " . strlen($imagen));
    echo $imagen;
    exit();
} else {
    http_response_code(404);
    exit('Imagen no encontrada');
}
