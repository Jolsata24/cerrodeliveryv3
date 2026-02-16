<?php 
session_start();
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CerroDelivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<div class="auth-page">
    <div class="card auth-card">
        <div class="card-body">
             
            <h3 class="card-title text-center mb-4">Acceso de Administrador</h3>
            <form action="../procesos/procesar_login_admin.php" method="POST">
                <div class="mb-3">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" required>
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
    </div>
</div>

</body>
</html>