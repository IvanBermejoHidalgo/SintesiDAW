CREATE TABLE User (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255) DEFAULT '/public/images/default-profile.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;SS

ALTER TABLE User 
ADD COLUMN gender VARCHAR(10) DEFAULT NULL;

ALTER TABLE User ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user';
--  PARA ASIGNAR ADMIN A USUARIO --
UPDATE User SET role = 'admin' WHERE id = 1; -- Asignar rol admin al usuario con id 1

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User(id)
);

ALTER TABLE messages ADD COLUMN image_path VARCHAR(255) DEFAULT NULL;

CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES User(id),
    FOREIGN KEY (message_id) REFERENCES messages(id),
    UNIQUE KEY unique_like (user_id, message_id)
);

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES User(id),
    FOREIGN KEY (message_id) REFERENCES messages(id)
);

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image_path VARCHAR(255) DEFAULT '/public/images/default-product.png',
    category ENUM('hombre','mujer','todos') DEFAULT 'todos'
);
CREATE TABLE imagenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    url LONGBLOB NOT NULL,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

CREATE TABLE carrito (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    producto_id INT NOT NULL,
    talla VARCHAR(5) NOT NULL,
    cantidad INT DEFAULT 1,
    FOREIGN KEY (usuario_id)   REFERENCES User(id)      ON DELETE CASCADE,
    FOREIGN KEY (producto_id)  REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE listas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES `User`(id)
);
CREATE TABLE lista_productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lista_id INT NOT NULL,
    producto_id INT NOT NULL,
    FOREIGN KEY (lista_id) REFERENCES listas(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);


CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    direccion VARCHAR(255) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    codigo_postal VARCHAR(20) NOT NULL,
    pais VARCHAR(100) NOT NULL,
    metodo_pago ENUM('card', 'paypal', 'cash') NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES User(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pedido_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    talla VARCHAR(5) NOT NULL,
    cantidad INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    INDEX (token),
    FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
);

ALTER TABLE pedidos MODIFY metodo_pago ENUM('card', 'cash') NOT NULL;

CREATE TABLE shared_lists (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    lista_id INT NOT NULL,
    shared_by INT NOT NULL,
    shared_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    is_public TINYINT(1) DEFAULT 0,
    FOREIGN KEY (message_id) REFERENCES messages(id),
    FOREIGN KEY (lista_id) REFERENCES listas(id),
    FOREIGN KEY (shared_by) REFERENCES User(id)
);

