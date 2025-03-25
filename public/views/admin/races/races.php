<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Carreras</title>
    <!-- Bootstrap CSS -->
    <link href="../../assets/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilos personalizados */
        body {
            background-color: #f8f9fa;
            padding-top: 56px; /* Ajuste para el navbar fijo */
        }
        .table-container {
            margin-top: 20px;
        }
        .btn-actions {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/admin/dashboard">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/dashboard">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/scuderias/teams">Gestionar Equipos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/drivers">Gestionar Pilotos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/races">Gestionar Carreras</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/logout">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="container table-container">
        <h1 class="mb-4">Gestionar Carreras</h1>

        <!-- Botón para añadir carrera -->
        <a href="/admin/add-race" class="btn btn-primary mb-3">Añadir Carrera</a>

        <!-- Tabla de carreras -->
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Gran Premio</th>
                    <th>Fecha</th>
                    <th>Ganador</th>
                    <th>Coche</th>
                    <th>Vueltas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                {% for race in races %}
                <tr>
                    <td>{{ race.grand_prix }}</td>
                    <td>{{ race.race_date }}</td>
                    <td>{{ race.winner }}</td>
                    <td>{{ race.team_name }}</td>
                    <td>{{ race.laps }}</td>
                    <td>
                        <a href="/admin/edit-race/{{ race.id }}" class="btn btn-warning btn-sm btn-actions">Editar</a>
                        <a href="/admin/races/delete/{{ race.id }}" class="btn btn-danger btn-sm">Eliminar</a>
                    </td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>