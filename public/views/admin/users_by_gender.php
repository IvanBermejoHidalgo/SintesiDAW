<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users by Gender</title>
    <link href="/assets/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Admin Panel</a>
            <div class="navbar-nav">
                <a class="nav-link" href="/admin/dashboard">Dashboard</a>
            </div>
            <div class="navbar-nav ms-auto">
                <a href="/admin/logout" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Users by Gender Distribution</h1>
        
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <canvas id="genderChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="/admin/dashboard" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('genderChart').getContext('2d');
            const genderData = <?= json_encode($genderStats) ?>;
            
            const labels = genderData.map(item => {
                switch(item.gender) {
                    case 'male': return 'Male';
                    case 'female': return 'Female';
                    case 'other': return 'Other';
                    default: return 'Not specified';
                }
            });
            
            const data = genderData.map(item => item.count);
            const colors = ['#36a2eb', '#ff6384', '#ffce56', '#4bc0c0'];
            
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>