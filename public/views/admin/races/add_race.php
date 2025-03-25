<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Carrera</title>
    <!-- Bootstrap CSS -->
    <link href="../../assets/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilos personalizados */
        body {
            background-color: #f8f9fa;
            padding-top: 56px; /* Ajuste para el navbar fijo */
        }
        .form-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-container h1 {
            margin-bottom: 20px;
            font-size: 2rem;
            font-weight: bold;
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
    <div class="form-container">
        <h1>Añadir Carrera</h1>
        <!-- add_race.php -->
        <!-- add_race.php -->
        <form method="POST" action="/admin/races/add">
            <div class="mb-3">
                <label for="grand_prix" class="form-label">Gran Premio:</label>
                <input type="text" class="form-control" id="grand_prix" name="grand_prix" required>
            </div>
            <div class="mb-3">
                <label for="race_date" class="form-label">Fecha:</label>
                <input type="date" class="form-control" id="race_date" name="race_date" required>
            </div>
            <div class="mb-3">
                <label for="winner" class="form-label">Ganador:</label>
                <select class="form-control" id="winner" name="winner" required>
                    <option value="">Selecciona un piloto</option>
                    {% for driver in drivers %}
                        <option value="{{ driver.last_name }}">{{ driver.last_name }}</option>
                    {% endfor %}
                </select>
            </div>
            <div class="mb-3">
                <label for="team_id" class="form-label">Equipo Ganador:</label>
                <select class="form-control" id="team_id" name="team_id" required>
                    <option value="">Selecciona un equipo</option>
                    {% for team in teams %}
                        <option value="{{ team.id }}">{{ team.team_name }}</option>
                    {% endfor %}
                </select>
            <div class="mb-3">
                <label for="laps" class="form-label">Vueltas:</label>
                <input type="number" class="form-control" id="laps" name="laps" required>
            </div>
            <button type="submit" class="btn btn-primary">Añadir Carrera</button>
        </form>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>