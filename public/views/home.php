<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home - Fórmula 1</title>
    <!-- Bootstrap CSS -->
    <link href="assets/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- <script src="../../node_modules/chart.js/dist/chart.umd.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Estilos personalizados -->
    <style>
        
        body {
            background-color: #f8f9fa;
            padding-top: 70px; /* Espacio para el menú fijo */
        }
        .navbar {
            background: linear-gradient(90deg, #e10600, #b90504);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 5px 0;
        }
        .navbar-brand {
            font-size: 1.2rem;
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        .navbar-brand img {
            height: 30px;
            margin-right: 8px;
            filter: brightness(0) invert(1);
        }
        .nav-link {
            color: white;
            font-weight: 500;
            margin: 0 8px;
            position: relative;
            transition: color 0.3s ease;
        }
        .nav-link:hover {
            color: #ffc107; /* Cambia el color del texto al pasar el cursor */
        }
        /* Restaurar la línea amarilla solo para las opciones del menú (no para el botón de idioma) */
        .nav-item:not(.language-dropdown) .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: #ffc107;
            bottom: -5px;
            left: 0;
            transition: width 0.3s ease;
        }
        .nav-item:not(.language-dropdown) .nav-link:hover::after {
            width: 100%;
        }
        .jumbotron {
            background: url('https://wallpapers.com/images/hd/formula-1-desktop-d9iowlxzig4c6kiw.jpg') no-repeat center center;
            background-size: cover;
            color: white;
            padding: 150px 25px;
            text-align: center;
            position: relative;
            margin-top: -70px;
        }
        .jumbotron::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5); /* Fondo oscuro para mejorar la legibilidad */
        }
        .jumbotron h1 {
            font-size: 4rem;
            font-weight: bold;
            position: relative;
            z-index: 1;
        }
        .jumbotron p {
            font-size: 1.5rem;
            position: relative;
            z-index: 1;
        }
        .section {
            padding: 80px 0;
        }
        .section h2 {
            margin-bottom: 30px;
            font-weight: bold;
            color: #e10600;
        }
        .section p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #555;
            text-align: justify; /* Texto justificado */
        }
        .section img {
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .text-container {
            border-radius: 10px; /* Bordes redondeados */
            padding: 20px; /* Espaciado interno */
            background-color: white; /* Fondo blanco */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); /* Sombra */
        }
        .footer {
            background-color: #1a1a1a;
            color: white;
            padding: 40px 0;
            margin-top: auto;
        }
        .footer a {
            color: #ffc107;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .footer a:hover {
            color: #e10600;
        }
        .footer-logo {
            width: 100px;
            margin-bottom: 20px;
        }
        .footer-section {
            margin-bottom: 20px;
        }
        .footer-section h5 {
            color: #ffc107;
            margin-bottom: 15px;
            font-size: 1.1rem;
            font-weight: bold;
        }
        .footer-section p {
            margin: 0;
            font-size: 0.9rem;
        }
        .footer-bottom {
            background-color: #000;
            padding: 10px 0;
            text-align: center;
            font-size: 0.9rem;
        }
        .footer-bottom p {
            margin: 0;
        }
        /* Estilos para el marco de la bandera */
        .language-dropdown .dropdown-toggle img {
            width: 32px; /* Aumentar el tamaño de la bandera */
            height: auto;
            border: 2px solid white; /* Marco blanco */
            border-radius: 4px; /* Bordes redondeados */
            padding: 2px; /* Espacio interno */
            background-color: white; /* Fondo blanco para mejorar la visibilidad */
        }
        /* Centrar vertical y horizontalmente la imagen de la bandera */
        .language-dropdown .dropdown-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem; /* Ajustar el padding para que se vea mejor */
            height: 100%; /* Asegurar que ocupe toda la altura del navbar */
        }
        /* Ajustar el margen del dropdown para que no se solape con otros elementos */
        .language-dropdown .dropdown-menu {
            min-width: 120px;
            margin-top: 8px; /* Ajustar el margen superior */
        }
        .language-dropdown .dropdown-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
        }
        .language-dropdown .dropdown-item img {
            width: 20px;
            margin-right: 8px;
        }
    
    </style>
</head>
<body>
    <!-- Barra de navegación fija -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/home">
                <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="Fórmula 1 Logo">
                
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/scuderias">{{ _("Scuderias") }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/pilotos">{{ _("Pilotos") }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/circuitos">{{ _("Circuitos") }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/logout">{{ _("Cerrar sesión") }}</a>
                    </li>
                    <li class="nav-item">
                    <li class="nav-item language-dropdown">
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <!-- Mostrar la bandera del idioma actual -->
                                <img src="{{ language == 'es' ? 'https://flagcdn.com/es.svg' : 'https://flagcdn.com/gb.svg' }}" alt="Idioma actual">
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                                <li>
                                    <form action="/change-language" method="post">
                                        <button type="submit" name="language" value="es" class="dropdown-item">
                                            <img src="https://flagcdn.com/es.svg" alt="Español"> Español
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form action="/change-language" method="post">
                                        <button type="submit" name="language" value="en" class="dropdown-item">
                                            <img src="https://flagcdn.com/gb.svg" alt="English"> English
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </li>

                    
                </ul>
            </div>
        </div>
    </nav>

    
    <div class="jumbotron">
        <div class="container">
            <h1>{{ _("Bienvenido a Fórmula 1") }}</h1>
            <p>{{ _("Explora el emocionante mundo de la Fórmula 1: equipos, pilotos y circuitos.") }}</p>
        </div>
    </div>

    <div class="container section">
        <div class="row">
            <div class="col-md-6">
                <div class="text-container">
                    <h2>{{ _("¿Qué es la Fórmula 1?") }}</h2>
                    <p>
                        {{ _("La Fórmula 1 es la categoría más alta del automovilismo deportivo. Es un campeonato mundial de carreras de monoplazas en el que compiten los mejores pilotos y equipos del mundo.") }}
                    </p>
                    <p>
                        {{ _("Cada temporada consta de una serie de carreras, conocidas como Grandes Premios, que se celebran en circuitos de todo el mundo. Los equipos compiten por el Campeonato de Constructores, mientras que los pilotos luchan por el Campeonato de Pilotos.") }}
                    </p>
                </div>
            </div>
            <div class="col-md-6">
                <img src="https://www.motor16.com/wp-content/uploads/2024/01/f1.jpg" class="img-fluid rounded" alt="Fórmula 1">
            </div>
        </div>
    </div>


    

    <!-- Sección de la gráfica de quesito -->
    <div class="container section">
        <div class="row">
            <div class="col-md-12">
                <h2>{{ _("Distribución de Victorias en la Temporada") }}</h2>
                    <div class="text-container">
                        <div class="chart-container" style="width: 500px; height: 500px; margin: 0 auto;">
                            <canvas id="winnersChart"></canvas>
                        </div>
                    </div>
            </div>
        </div>
    </div>



    <div class="container section">
        <div class="row">
            <div class="col-md-12">
                <h2>{{ _("Distribución de Victorias por Equipos en la Temporada") }}</h2>
                <div class="text-container">
                    <div class="chart-container" style="width: 500px; height: 500px; margin: 0 auto;">
                        <canvas id="teamWinnersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>


    

    <!-- Pie de página -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 footer-section">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="Fórmula 1 Logo" class="footer-logo">
                    <p>{{ _("La máxima categoría del automovilismo. Descubre todo sobre las escuderías, pilotos y circuitos.") }}</p>
                    
                </div>
                <div class="col-md-4 footer-section">
                    <h5>{{ _("Enlaces rápidos") }}</h5>
                    <ul class="list-unstyled">
                        <li><a href="/scuderias">{{ _("Scuderias") }}</a></li>
                        <li><a href="/pilotos">{{ _("Pilotos") }}</a></li>
                        <li><a href="/circuitos">{{ _("Circuitos") }}</a></li>
                    </ul>
                </div>
                <div class="col-md-4 footer-section">
                    <h5>{{ _("Contacto") }}</h5>
                    <p>Email: <a href="mailto:ibermejo@elpuig.xeill.net">ibermejo@elpuig.xeill.net</a></p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Definir las variables labels y data
        const labels = {{ labels_js|raw }};
        const data = {{ data_js|raw }};
    </script>

    <!-- Script para la gráfica -->
    <script>
        const ctx = document.getElementById('winnersChart').getContext('2d');
        const winnersChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Número de Victorias',
                    data: data,
                    backgroundColor: [
                    '#FF6384', // Rosa
                    '#36A2EB', // Azul
                    '#FFCE56', // Amarillo
                    '#4BC0C0', // Turquesa
                    '#9966FF', // Morado
                    '#FF9F40', // Naranja
                    '#C9CBCF', // Gris
                    '#77DD77', // Verde claro
                    '#FF6961', // Rojo claro
                    '#FDFD96'  // Amarillo claro
                ],
                borderColor: '#ffffff', // Borde blanco
                borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Distribución de Victorias en la Temporada'
                    }
                }
            }
        });
    </script>

    <script>
        const teamLabels = {{ teamLabels_js|raw }};
        const teamData = {{ teamData_js|raw }};

        const teamCtx = document.getElementById('teamWinnersChart').getContext('2d');
        const teamWinnersChart = new Chart(teamCtx, {
            type: 'pie',
            data: {
                labels: teamLabels,
                datasets: [{
                    label: 'Número de Victorias por Equipos',
                    data: teamData,
                    backgroundColor: [
                        '#FF6384', // Rosa
                        '#36A2EB', // Azul
                        '#FFCE56', // Amarillo
                        '#4BC0C0', // Turquesa
                        '#9966FF', // Morado
                        '#FF9F40', // Naranja
                        '#C9CBCF', // Gris
                        '#77DD77', // Verde claro
                        '#FF6961', // Rojo claro
                        '#FDFD96'  // Amarillo claro
                    ],
                    borderColor: '#ffffff', // Borde blanco
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Distribución de Victorias por Equipos en la Temporada'
                    }
                }
            }
        });
    </script>
</body>
</html>