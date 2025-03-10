<!doctype html>
<html lang="en" data-bs-theme="auto">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión</title>

    <!-- Bootstrap CSS -->
    <link href="../assets/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
      body {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background-color: #f5f5f5;
      }
      .card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      }
      .btn-primary {
        background: #4e73df;
        border: none;
        transition: background 0.3s ease;
      }
      .btn-primary:hover {
        background: #2e59d9;
      }
    </style>
  </head>
  <body>

    <div class="card p-4" style="max-width: 400px; width: 100%;">
      <div class="card-body">
        <h4 class="card-title text-center mb-4">Iniciar Sesión</h4>
        
        <form method="POST" action="/">
          <div class="mb-3">
            <label for="username" class="form-label">Nombre de usuario</label>
            <input type="text" class="form-control" id="username" name="username" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
        </form>

        <hr>
        <p class="text-center">
          ¿No tienes cuenta? <a href="/signup">Regístrate</a>
        </p>
      </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="../assets/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>