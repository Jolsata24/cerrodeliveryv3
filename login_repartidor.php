<?php
session_start();
// Si el repartidor ya inició sesión, redirigirlo a su dashboard
if (isset($_SESSION['repartidor_id'])) {
    header('Location: repartidor/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Repartidores - CerroDelivery</title>
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
            <h2 class="card-title text-center mb-4">Acceso Repartidores</h2>

            <?php if(isset($_GET['status']) && $_GET['status'] == 'solicitud_enviada'): ?>
                <div class="alert alert-success">Tu solicitud ha sido enviada. Se te notificará cuando sea aprobada.</div>
            <?php endif; ?>
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

            <form action="procesos/procesar_login_repartidor.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button type="submit" class="btn btn-info w-100 text-white">Ingresar</button>
                <a href="index.php" class="btn w-100 mt-3 rounded-pill"
                        style="font-weight: 600; border: 2px solid #e5e7eb; background-color: #fff; color: #6b7280; transition: all 0.3s ease; text-decoration: none;">
                        <i class="bi bi-arrow-left me-1"></i> Volver al Inicio
                    </a>
            </form>
        </div>
        <p class="text-center mt-3 mb-0">¿Aún no te unes? <a href="registro_repartidor.php">Conviértete en repartidor</a></p>
    </div>
</div>

</body>
</html>