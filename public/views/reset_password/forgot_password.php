<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recuperar contraseña</title>
  <link href="/assets/twbs/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
  <div class="card p-4" style="width: 100%; max-width: 400px;">
    <div class="card-body">
      <h4 class="text-center mb-4">¿Olvidaste tu contraseña?</h4>
      {% if message %}
        <div class="alert alert-info">{{ message }}</div>
      {% endif %}
      <form method="POST">
        <div class="mb-3">
          <label for="email" class="form-label">Introduce tu email</label>
          <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Enviar enlace</button>
      </form>
      <div class="text-center mt-3">
        <a href="/">Volver al login</a>
      </div>
    </div>
  </div>
</body>
</html>