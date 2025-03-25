<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Equipos</title>
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
    <!-- Navbar (opcional, si tienes uno) -->
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
        <h1 class="mb-4">Gestionar Equipos</h1>

        <!-- Botón para añadir equipo -->
        <a href="/admin/add-team" class="btn btn-primary mb-3">Añadir Equipo</a>

        <!-- Tabla de equipos -->
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Piloto 1</th>
                    <th>Piloto 2</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                {% for team in teams %}
                <tr>
                    <td>{{ team.team_name }}</td>
                    <td>
                        {% if team.drivers[0] %}
                            {{ team.drivers[0].first_name }} {{ team.drivers[0].last_name }}
                        {% else %}
                            Sin piloto
                        {% endif %}
                    </td>
                    <td>
                        {% if team.drivers[1] %}
                            {{ team.drivers[1].first_name }} {{ team.drivers[1].last_name }}
                        {% else %}
                            Sin piloto
                        {% endif %}
                    </td>
                    <td>
                        <a href="/admin/edit-team/{{ team.id }}" class="btn btn-warning btn-sm btn-actions">Editar</a>
                        <a href="/admin/scuderias/delete/{{ team.id }}" class="btn btn-danger btn-sm">Eliminar</a>
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