<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conviértete en Repartidor - CerroDelivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <h2 class="card-title text-center mb-2">Conviértete en Repartidor</h2>
            <p class="text-center text-muted mb-4">Tu cuenta será revisada antes de ser activada.</p>
            <form action="procesos/procesar_registro_repartidor.php" method="POST">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre Completo</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
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
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono" required>
                </div>
                <button type="submit" class="btn btn-info w-100 text-white">Enviar Solicitud</button>
                <a href="index.php" class="btn w-100 mt-3 rounded-pill"
                        style="font-weight: 600; border: 2px solid #e5e7eb; background-color: #fff; color: #6b7280; transition: all 0.3s ease; text-decoration: none;">
                        <i class="bi bi-arrow-left me-1"></i> Volver al Inicio
                    </a>
            </form>
        </div>
        <p class="text-center mt-3 mb-0">¿Ya tienes una cuenta? <a href="login_repartidor.php">Inicia Sesión</a></p>
        
    </div>
</div>

</body>
</html>