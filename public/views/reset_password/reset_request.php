<?php
require_once 'SessionController.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $message = SessionController::sendResetPasswordEmail($email);
}
?>

<!DOCTYPE html>
<html>
<head><title>Restablecer contraseña</title></head>
<body>
<h2>Solicitar restablecimiento de contraseña</h2>
<form method="post">
    <input type="email" name="email" required placeholder="Tu correo electrónico">
    <button type="submit">Enviar</button>
</form>
<p><?= htmlspecialchars($message) ?></p>
</body>
</html>
