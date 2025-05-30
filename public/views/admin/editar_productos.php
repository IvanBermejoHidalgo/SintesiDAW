<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestionar Productos</title>
    <!-- Bootstrap CSS -->
    <link href="../../assets/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 56px; /* navbar fijo */
        }
        .table-container {
            margin-top: 20px;
        }
        .btn-actions {
            margin-right: 5px;
        }
        img.product-img {
            max-width: 100px;
            max-height: 80px;
            object-fit: contain;
        }
        .product-img {
            width: 100px;
            height: 80px;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/admin/dashboard">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="/admin/dashboard">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="/admin/editar-productos">Gestionar Productos</a></li>
                    <li class="nav-item"><a class="nav-link" href="/admin/logout">Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="container table-container">
        <h1 class="mb-4">Gestionar Productos</h1>

        <!-- Botón añadir producto -->
        <a href="/admin/crear-producto" class="btn btn-primary mb-3">Añadir Producto</a>

        <!-- Tabla productos -->
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Categoría</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                {% for producto in productos %}
                <tr>
                    <td>
                        {% if producto.imagenes_base64 is not empty %}
                            <img src="data:image/jpeg;base64,{{ producto.imagenes_base64[0] }}" 
                                alt="Imagen {{ producto.name }}" 
                                class="product-img img-thumbnail" />
                        {% else %}
                            <img src="/images/default-product.png" 
                                alt="Imagen por defecto {{ producto.name }}" 
                                class="product-img img-thumbnail" />
                        {% endif %}
                    </td>
                    <td>{{ producto.name }}</td>
                    <td>{{ producto.description | length > 80 ? producto.description[:80] ~ '...' : producto.description }}</td>
                    <td>{{ producto.price | number_format(2, '.', ',') }} €</td>
                    <td class="text-capitalize">{{ producto.category }}</td>
                    <td>
                        <a href="/admin/editar-producto/{{ producto.id }}" class="btn btn-warning btn-sm btn-actions">Editar</a>
                        <a href="/admin/eliminar-producto/{{ producto.id }}" class="btn btn-danger btn-sm">Eliminar</a>
                    </td>
                </tr>
                {% else %}
                <tr><td colspan="6" class="text-center">No hay productos disponibles.</td></tr>
                {% endfor %}
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
