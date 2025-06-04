<?php

class ImagenService {
    public function getImagenesBase64($producto_id) {
        $db = DatabaseController::connect();
        $stmt = $db->prepare("SELECT url FROM imagenes WHERE producto_id = ?");
        $stmt->execute([$producto_id]);
        $imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $imagenes_base64 = [];
        foreach ($imagenes as $imagen) {
            $path = $imagen['url'];
            if (!empty($path)) {
                if (file_exists($path)) {
                    $imgData = file_get_contents($path);
                    $imagenes_base64[] = base64_encode($imgData);
                } else {
                    $imagenes_base64[] = base64_encode($path); // fallback
                }
            }
        }

        return $imagenes_base64;
    }
}
