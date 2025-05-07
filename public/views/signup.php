<!doctype html>
<html lang="en" data-bs-theme="auto">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro</title>

    <!-- Bootstrap CSS -->
    <link href="assets/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    

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
        background: #0d6efd; /* Cambiado a azul Bootstrap */
        border: none;
        transition: background 0.3s ease;
      }
      .btn-primary:hover {
        background: #0b5ed7; /* Azul más oscuro al hacer hover */
      }
      .logo-f1 {
        display: block;
        margin: 20px auto 30px; /* Margen superior de 20px y margen inferior de 30px */
        max-width: 100px; /* Tamaño del logo */
      }
    </style>
  </head>
  <body>

    <div class="card p-4" style="max-width: 400px; width: 100%;">
      <div class="card-body">
      <img src="images/logo.png" alt="Logo ShopList" class="logo-f1">
        <!-- FORMULARIO PARA PODER REGISTRARSE -->
        <form method="POST" action="/signup">
          <div class="mb-3">
            <label for="username" class="form-label">Nombre de usuario</label>
            <input type="text" class="form-control" id="username" name="username" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Correo Electrónico</label>
            <input type="email" class="form-control" id="email" name="email" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <div class="mb-3">
            <div class="gender-options">
              <div class="gender-option">
                <input type="radio" id="male" name="gender" value="male" required>
                <label for="male">Masculino</label>
                <input type="radio" id="female" name="gender" value="female">
                <label for="female">Femenino</label>
              </div>
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100">Registrarse</button>
        </form>

        <hr>
        <p class="text-center">
          ¿Ya tienes cuenta? <a href="/">Inicia sesión</a>
        </p>
      </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="assets/twbs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
