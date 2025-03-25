<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="../assets/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilos personalizados */
        body {
            background-color: #f8f9fa;
            padding-top: 56px; /* Ajuste para el navbar fijo */
        }
        .navbar {
            background-color: #343a40; /* Color de fondo del navbar */
        }
        .navbar-brand, .nav-link {
            color: #ffffff !important; /* Color del texto en el navbar */
        }
        .nav-link:hover {
            color: #ffc107 !important; /* Color al pasar el mouse */
        }
        .section-title {
            margin-top: 20px;
            margin-bottom: 20px;
            font-size: 2rem;
            font-weight: bold;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #343a40;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/admin/dashboard">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/logout">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="container">
        <h1 class="section-title">Bienvenido</h1>

        <!-- Estadísticas -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        Equipos
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ teams|length }}</h5>
                        <p class="card-text">Número de equipos registrados.</p>
                        <a href="/admin/scuderias/teams" class="btn btn-primary">Gestionar Equipos</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        Pilotos
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ drivers|length }}</h5>
                        <p class="card-text">Número de pilotos registrados.</p>
                        <a href="/admin/drivers/drivers" class="btn btn-primary">Gestionar Pilotos</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        Carreras
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ races|length }}</h5>
                        <p class="card-text">Número de carreras registradas.</p>
                        <a href="/admin/races" class="btn btn-primary">Gestionar Carreras</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS y dependencias -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>