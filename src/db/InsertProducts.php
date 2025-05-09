<?php
$conexion = new mysqli("localhost", "usuario", "usuario", "ShopList");
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$csv = fopen("productos.csv", "r");
if (!$csv) {
    die("No se pudo abrir el archivo CSV.");
}

fgetcsv($csv, 0, ","); // Saltar encabezado

while (($datos = fgetcsv($csv, 0, ",")) !== false) {
    $name = $datos[0];
    $description = $datos[1];
    $price = $datos[2];
    $category = $datos[6]; // 'hombre', 'mujer', 'todos'

    // Insertar el producto
    $stmt = $conexion->prepare("INSERT INTO productos (name, description, price, category) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $name, $description, $price, $category);
    $stmt->execute();
    $producto_insertado_id = $stmt->insert_id;
    $stmt->close();

    // Insertar las imágenes asociadas (de la columna 3 a la 5)
    for ($i = 3; $i <= 5; $i++) {
        $url = trim($datos[$i] ?? '');
        if (!empty($url)) {
            $image_data = @file_get_contents($url);
if ($image_data !== false) {
    $stmt_img = $conexion->prepare("INSERT INTO imagenes (producto_id, url) VALUES (?, ?)");
    
    $stmt_img->bind_param("ib", $producto_insertado_id, $image_data);  // este valor será reemplazado
    $stmt_img->send_long_data(1, $image_data);                         // ahora envías el binario real
    $stmt_img->execute();
    $stmt_img->close();

                echo "✅ Imagen añadida para producto $producto_insertado_id: $url<br>";
            } else {
                echo "❌ No se pudo descargar la imagen de $url<br>";
            }
        }
    }
}

fclose($csv);
$conexion->close();
?>
