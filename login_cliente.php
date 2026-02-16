<?php
session_start();
// Si el cliente ya inició sesión, redirigirlo al index
if (isset($_SESSION['cliente_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - CerroDelivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>

    <div class="auth-page">
        <div class="card auth-card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <a href="index.php">
                        <img src="assets/img/logo.png" alt="CerroDelivery Logo" style="height: 50px;">
                    </a>
                </div>
                <h2 class="card-title text-center mb-4">Bienvenido de Vuelta</h2>

                <?php if (isset($_GET['status']) && $_GET['status'] == 'registrado'): ?>
                    <div class="alert alert-success">¡Cuenta creada! Ya puedes iniciar sesión.</div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">Usuario o contraseña incorrectos.</div>
                <?php endif; ?>

                <form action="procesos/procesar_login_cliente.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                    <a href="index.php" class="btn w-100 mt-3 rounded-pill"
                        style="font-weight: 600; border: 2px solid #e5e7eb; background-color: #fff; color: #6b7280; transition: all 0.3s ease; text-decoration: none;">
                        <i class="bi bi-arrow-left me-1"></i> Volver al Inicio
                    </a>
                </form>
            </div>
            <p class="text-center mt-3 mb-0">¿Aún no tienes cuenta? <a href="registro_cliente.php">Crea una aquí</a></p>
        </div>
    </div>

</body>

</html>