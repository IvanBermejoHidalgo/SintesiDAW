# 🛠️ ShopList

Aplicación web completa que combina una tienda online con funcionalidades de red social. Los usuarios pueden registrarse, iniciar sesión, navegar productos por categorías, añadir productos a listas personalizadas y realizar pedidos. Además, cada usuario dispone de un perfil personal, dar "like" y comentar. El sistema incluye un modo admin en el que se muestran estadísticas, gráficos interactivos y un modo oscuro para mejorar la experiencia de usuario.

## 🚀 Tecnologías utilizadas

- **PHP**
- **Apache**
- **Twig** (motor de plantillas)
- **Bootstrap**
- **MySQL**
- **Routing personalizado**
- **Gráficos con Chart.js / ApexCharts**
- **Estadísticas en tiempo real**
- **Soporte para modo oscuro (Dark Mode)**

## 📁 Estructura del proyecto
```bash
ShopList/
├── public/
│ ├── css/
│ │ │ ├── estilos.css
│ ├── views/
│ │ ├── admin/
│ │ │ ├── crear_producto.php
│ │ │ ├── dashboard.php
│ │ │ ├── editar_producto.php
│ │ │ ├── editar_productos.php
│ │ │ ├── imagen.php
│ │ │ ├── login.php
│ │ │ ├── products_by_category.php
│ │ │ ├── users_by_gender.php
│ │ ├── navegacion/
│ │ │ ├── error.html
│ │ │ ├── footer.html
│ │ ├── reset_password/
│ │ │ ├── forgot_password.php
│ │ │ ├── invalid_token.html
│ │ │ ├── reset_form.html
│ │ │ ├── reset_request.php
│ │ │ ├── reset_success.html
│ │ ├── tienda/
│ │ │ ├── carrito.html
│ │ │ ├── checkout.html
│ │ │ ├── mis_listas.html
│ │ │ ├── mis_pedidos.html
│ │ │ ├── pedido_confirmado.html
│ │ │ ├── producto.html
│ │ │ ├── tienda.html
│ │ ├── 404.php
│ │ ├── aboutus.html
│ │ ├── home.html
│ │ ├── login.php
│ │ ├── message_card.html
│ │ ├── profile.html
│ │ ├── signup.php
│ │ ├── user.html
│ ├── .htaccess
│ ├── admin.php
│ ├── index.php
├── src/
│ ├── controller/
│ │ ├── AdminController.php
│ │ ├── AuthController.php
│ │ ├── CartController.php
│ │ ├── CheckoutController.php
│ │ ├── DatabaseController.php
│ │ ├── HomeController.php
│ │ ├── MisListasController.php
│ │ ├── PedidoConfirmadoController.php
│ │ ├── PedidosController.php
│ │ ├── ProfileController.php
│ │ ├── SessionController.php
│ │ ├── TiendaController.php
│ ├── repositories/
│ │ ├── AdminRepository.php
│ │ ├── AuthRepository.php
│ │ ├── CartRepository.php
│ │ ├── CheckoutRepository.php
│ │ ├── HomeRepository.php
│ │ ├── ImageRepository.php
│ │ ├── MisListasRepository.php
│ │ ├── PasswordResetRepository.php
│ │ ├── PedidoRepository.php
│ │ ├── PedidosRepository.php
│ │ ├── ProfileRepository.php
│ │ ├── TiendaRepository.php
│ │ ├── UserRepository.php
│ ├── services/
│ │ ├── AdminService.php
│ │ ├── AuthService.php
│ │ ├── CartService.php
│ │ ├── CheckoutService.php
│ │ ├── HomeService.php
│ │ ├── ImageService.php
│ │ ├── MisListasService.php
│ │ ├── PedidoConfirmadoService.php
│ │ ├── PedidosService.php
│ │ ├── ProfileService.php
│ │ ├── TiendaService.php
│ │ ├── SessionService.php
│ ├── db/
│ │ ├── InsertProducts.php
│ │ ├── productos.csv
│ │ ├── tablas.sql

```

## 🧪 Funcionalidades clave

- 🔐 Inicio de sesión y registro
- 🧑🤝🧑 Red social: seguir usuarios, dar like a listas, comentar
- 🛒 Tienda para comprar productos por categorias
- 📊 Dashboard con estadísticas de usuarios, compras y productos (Admin)
- 👤 Perfil de usuario con foto, bio y actividad
- 📦 Sección "Mis pedidos" con historial y detalles
- 📈 Gráficos por categoría, género, etc.
- 🌙 Modo oscuro
- 📁 Gestión de imágenes en base64
- 📉 Visualización de listas con los productos añadidos


## Contacto

- **Nombre:** Sara Essakkal Martínez | Iván Bermejo Hidalgo
- **Email:** sessakkal@elpuig.xeill.net | ibermejo@elpuig.xeill.net
