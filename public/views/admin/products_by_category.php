<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos por Categoría</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="/assets/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        h2 {
            color: #343a40;
            margin-bottom: 30px;
        }
        .chart-container {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="chart-container">
            <h2 class="text-center">Distribución de Productos por Categoría</h2>
            <canvas id="productChart" height="100"></canvas>
            <div class="text-center">
                <a href="/admin/dashboard" class="btn btn-outline-primary mt-4">← Volver al Dashboard</a>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('productChart').getContext('2d');
        const productChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Hombre', 'Mujer', 'Unisex'],
                datasets: [{
                    label: 'Número de productos',
                    data: [{{ hombreCount }}, {{ mujerCount }}, {{ todosCount }}],
                    backgroundColor: [
                        'rgba(0, 123, 255, 0.7)',   // azul - Hombre
                        'rgba(220, 53, 69, 0.7)',   // rojo - Mujer
                        'rgba(40, 167, 69, 0.7)'    // verde - Unisex
                    ],
                    borderColor: [
                        'rgba(0, 123, 255, 1)',
                        'rgba(220, 53, 69, 1)',
                        'rgba(40, 167, 69, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        enabled: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
