<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Dashboard</title>
<link href="/assets/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" />

<style>
    .bg-usuarios { background-color: #4a90e2; color: white; }
    /* Opcional: bordes redondeados suaves para más elegancia */
    .card {
        border-radius: 0.5rem;
    }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">Admin Panel</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="/admin/dashboard">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/editar-productos">Editar productos</a>
                </li>
            </ul>
            <a href="/admin/logout" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h1>Dashboard</h1>

    <div class="row mt-4">
        <div class="col-md-4">
            <a href="/admin/users-by-gender" style="text-decoration: none;">
                <div class="card bg-usuarios mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Usuarios Totales</h5>
                        <p class="card-text display-4">{{ userCount }}</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="/admin/products-by-category" style="text-decoration: none;">
                <div class="card bg-usuarios mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Productos Totales</h5>
                        <p class="card-text display-4">{{ productCount }}</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <div class="card bg-usuarios mb-3">
                <div class="card-body">
                    <h5 class="card-title">Mensajes Totales</h5>
                    <p class="card-text display-4">{{ messageCount }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-usuarios mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total de Compras (€)</h5>
                    <p class="card-text display-4">€ {{ totalCompras|number_format(2, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-usuarios mb-3">
                <div class="card-body">
                    <h5 class="card-title">Cantidad Total de Compras</h5>
                    <p class="card-text display-4">{{ totalCantidadCompras }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
                <div class="card text-white bg-usuarios mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Listas Creadas</h5>
                        <p class="card-text display-4">{{ totalListas }}</p>
                    </div>
                </div>
            </a>
        </div>

    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5>Usuario más activos</h5>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre de usuario</th>
                        <th>Mensajes</th>
                    </tr>
                </thead>
                <tbody>
                    {% for user in activeUsers %}
                    <tr>
                        <td>{{ user.username }}</td>
                        <td>{{ user.message_count }}</td>
                    </tr>
                    {% endfor %}
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>