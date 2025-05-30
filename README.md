# 🛠️ Panel de Administración - Proyecto Síntesi

Este proyecto es un panel de administración web construido con **PHP**, **Twig**, **Bootstrap**, y **MySQL**, desarrollado como parte del proyecto de síntesi. Incluye autenticación de administradores, gestión de productos y estadísticas dinámicas con visualizaciones.

## 🚀 Tecnologías utilizadas

- **PHP 8.x**
- **Twig** (motor de plantillas)
- **Bootstrap 5**
- **MySQL**
- **Routing personalizado**
- **Gráficos con Chart.js / ApexCharts**
- **Estadísticas en tiempo real**
- **Soporte para modo oscuro (Dark Mode)**

## 📁 Estructura del proyecto

/public
index.php
admin.php
/views
/admin
dashboard.php
editar_productos.php
crear_producto.php
login.php
...
/src
/controller
AdminController.php
DatabaseController.php
/vendor


## 🧪 Funcionalidades clave

- 🔐 Login de administrador con sesión
- 📊 Dashboard con estadísticas de usuarios, compras y productos
- 🛒 CRUD de productos (crear, editar, eliminar)
- 📈 Gráficos por categoría, género, etc.
- 🌙 Modo oscuro con variables CSS personalizadas
- 📁 Gestión de imágenes en base64
- 📉 Visualización de listas de usuarios y sus productos

## ✅ Requisitos

- PHP 8.x
- MySQL
- Apache (con soporte para `.htaccess`)
- Composer
