<?php
session_start();
// El "guardia de seguridad"
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../includes/conexion.php';

// Consulta para RESTAURANTES
$sql_restaurantes = "SELECT id, nombre_restaurante, email, estado, fecha_vencimiento_suscripcion FROM restaurantes ORDER BY fecha_registro DESC";
$resultado_restaurantes = $conn->query($sql_restaurantes);

// CONSULTA PARA REPARTIDORES PENDIENTES
$sql_repartidores = "SELECT id, nombre, email, telefono FROM repartidores WHERE estado_aprobacion = 'pendiente' ORDER BY id ASC";
$resultado_repartidores = $conn->query($sql_repartidores);

// Verificar estado actual del Modo Lluvia para mostrar el switch correctamente
// Asegúrate de haber creado la tabla 'configuracion'
$estado_lluvia = '0';
$check_lluvia = $conn->query("SELECT valor FROM configuracion WHERE clave = 'modo_lluvia'");
if ($check_lluvia && $row = $check_lluvia->fetch_assoc()) {
    $estado_lluvia = $row['valor'];
}

include '../includes/header.php';
?>

<div class="dashboard-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3">Panel de Administración</h2>
        <p class="text-muted mb-0">Gestión de la plataforma CerroDelivery.</p>
    </div>
    <a href="../procesos/logout_admin.php" class="btn btn-outline-danger">Cerrar Sesión</a>
</div>

<div class="card mb-4 border-primary shadow-sm">
    <div class="card-body d-flex justify-content-between align-items-center bg-light rounded">
        <div>
            <h5 class="mb-1 fw-bold text-primary"><i class="bi bi-cloud-lightning-rain-fill me-2"></i>Modo Tormenta / Lluvia</h5>
            <p class="mb-0 small text-muted">Al activar esto, se cobrará un <strong>recargo extra</strong> a todos los envíos para compensar a los motorizados.</p>
        </div>
        <div class="form-check form-switch text-center">
            <input class="form-check-input" type="checkbox" id="switchLluvia" style="width: 3.5em; height: 1.8em; cursor: pointer;" 
                <?php echo ($estado_lluvia == '1') ? 'checked' : ''; ?> onchange="cambiarModoLluvia(this)">
            <br>
            <label class="form-check-label fw-bold mt-1" for="switchLluvia" id="textoSwitch">
                <?php echo ($estado_lluvia == '1') ? '<span class="text-success">ACTIVADO</span>' : '<span class="text-muted">Desactivado</span>'; ?>
            </label>
        </div>
    </div>
</div>

<div class="card dashboard-card mb-4">
    <div class="card-header">Gestionar Restaurantes</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Restaurante</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Vencimiento</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado_restaurantes->num_rows > 0): ?>
                        <?php while($restaurante = $resultado_restaurantes->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($restaurante['nombre_restaurante']); ?></td>
                                <td><?php echo htmlspecialchars($restaurante['email']); ?></td>
                                <td>
                                    <?php if ($restaurante['estado'] == 'activo'): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $restaurante['fecha_vencimiento_suscripcion'] ? date('d/m/Y', strtotime($restaurante['fecha_vencimiento_suscripcion'])) : 'Nunca'; ?></td>
                                <td class="text-end">
                                    <a href="activar_restaurante.php?id=<?php echo $restaurante['id']; ?>" class="btn btn-primary btn-sm">
                                        Activar / Renovar (30 días)
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center p-4 text-muted">No hay restaurantes registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card dashboard-card">
    <div class="card-header">
        Aprobar Nuevos Repartidores
        <?php if ($resultado_repartidores->num_rows > 0): ?>
            <span class="badge bg-warning text-dark ms-2"><?php echo $resultado_repartidores->num_rows; ?> pendiente(s)</span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nombre del Repartidor</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th class="text-end">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado_repartidores->num_rows > 0): ?>
                        <?php while($repartidor = $resultado_repartidores->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($repartidor['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($repartidor['email']); ?></td>
                                <td><?php echo htmlspecialchars($repartidor['telefono']); ?></td>
                                <td class="text-end">
                                    <a href="aprobar_repartidor.php?id=<?php echo $repartidor['id']; ?>" class="btn btn-success btn-sm">Aprobar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center p-4 text-muted">No hay solicitudes de repartidores pendientes.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function cambiarModoLluvia(checkbox) {
    const estado = checkbox.checked ? '1' : '0';
    const label = document.getElementById('textoSwitch');
    
    // Feedback visual inmediato
    label.innerHTML = '<span class="text-warning">Guardando...</span>';
    checkbox.disabled = true;
    
    const formData = new FormData();
    formData.append('estado', estado);

    fetch('cambiar_estado_lluvia.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        checkbox.disabled = false;
        if(data.status === 'success') {
            if(estado === '1') {
                label.innerHTML = '<span class="text-success">ACTIVADO</span>';
                // Opcional: Sonido o alerta
            } else {
                label.innerHTML = '<span class="text-muted">Desactivado</span>';
            }
        } else {
            alert("Error: " + data.msg);
            checkbox.checked = !checkbox.checked; // Revertir cambio
            label.innerHTML = 'Error';
        }
    })
    .catch(err => {
        console.error(err);
        alert("Error de conexión");
        checkbox.disabled = false;
        checkbox.checked = !checkbox.checked;
    });
}
</script>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>