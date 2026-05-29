<?php
// --- Lógica PHP (Modificada) ---
session_start();
if (!isset($_SESSION['restaurante_id'])) {
    header('Location: ../login_restaurante.php');
    exit();
}
require_once '../includes/conexion.php';
$id_restaurante_actual = $_SESSION['restaurante_id'];

// Consultas para datos del restaurante
$sql_restaurante = "SELECT hora_apertura, hora_cierre, hora_apertura_sab, hora_cierre_sab, hora_apertura_dom, hora_cierre_dom, telefono, yape_numero, yape_qr, latitud, longitud FROM restaurantes WHERE id = ?";
$stmt_restaurante = $conn->prepare($sql_restaurante);
$stmt_restaurante->bind_param("i", $id_restaurante_actual);
$stmt_restaurante->execute();
$restaurante_data = $stmt_restaurante->get_result()->fetch_assoc();
$stmt_restaurante->close();

// Obtener los platos del restaurante
$sql_platos = "SELECT * FROM menu_platos WHERE id_restaurante = ? ORDER BY id DESC";
$stmt_platos = $conn->prepare($sql_platos);
$stmt_platos->bind_param("i", $id_restaurante_actual);
$stmt_platos->execute();
$resultado_platos = $stmt_platos->get_result();

// 1. Obtener TODAS las categorías posibles (para el formulario)
$sql_all_cats = "SELECT * FROM categorias ORDER BY nombre_categoria ASC";
$res_all_cats = $conn->query($sql_all_cats);

// 2. Obtener las categorías QUE YA TIENE el restaurante (para marcarlas)
$sql_my_cats = "SELECT id_categoria FROM restaurante_categorias WHERE id_restaurante = ?";
$stmt_my_cats = $conn->prepare($sql_my_cats);
$stmt_my_cats->bind_param("i", $id_restaurante_actual);
$stmt_my_cats->execute();
$res_my_cats = $stmt_my_cats->get_result();

$mis_categorias_ids = [];
while ($row = $res_my_cats->fetch_assoc()) {
    $mis_categorias_ids[] = $row['id_categoria'];
}
$stmt_my_cats->close();

// ==========================================
// NUEVAS CONSULTAS PARA GRÁFICOS Y ESTADÍSTICAS
// ==========================================

// Ganancias y ventas - HOY
$sql_hoy = "SELECT COUNT(id) as total_ventas, COALESCE(SUM(monto_total - costo_envio), 0) as ganancias FROM pedidos WHERE id_restaurante = ? AND estado_pedido = 'Entregado' AND DATE(fecha_pedido) = CURDATE()";
$stmt_hoy = $conn->prepare($sql_hoy);
$stmt_hoy->bind_param("i", $id_restaurante_actual);
$stmt_hoy->execute();
$stats_hoy = $stmt_hoy->get_result()->fetch_assoc();
$stmt_hoy->close();

// Ganancias y ventas - SEMANA (Asumiendo que la semana empieza el lunes)
$sql_semana = "SELECT COUNT(id) as total_ventas, COALESCE(SUM(monto_total - costo_envio), 0) as ganancias FROM pedidos WHERE id_restaurante = ? AND estado_pedido = 'Entregado' AND YEARWEEK(fecha_pedido, 1) = YEARWEEK(CURDATE(), 1)";
$stmt_semana = $conn->prepare($sql_semana);
$stmt_semana->bind_param("i", $id_restaurante_actual);
$stmt_semana->execute();
$stats_semana = $stmt_semana->get_result()->fetch_assoc();
$stmt_semana->close();

// Ganancias y ventas - MES
$sql_mes = "SELECT COUNT(id) as total_ventas, COALESCE(SUM(monto_total - costo_envio), 0) as ganancias FROM pedidos WHERE id_restaurante = ? AND estado_pedido = 'Entregado' AND MONTH(fecha_pedido) = MONTH(CURDATE()) AND YEAR(fecha_pedido) = YEAR(CURDATE())";
$stmt_mes = $conn->prepare($sql_mes);
$stmt_mes->bind_param("i", $id_restaurante_actual);
$stmt_mes->execute();
$stats_mes = $stmt_mes->get_result()->fetch_assoc();
$stmt_mes->close();

// Datos para el Gráfico (Días de la semana actual: Lunes a Domingo)
$ventas_dias = [0, 0, 0, 0, 0, 0, 0]; 
$sql_grafico = "SELECT WEEKDAY(fecha_pedido) as dia, COALESCE(SUM(monto_total - costo_envio), 0) as total FROM pedidos WHERE id_restaurante = ? AND estado_pedido = 'Entregado' AND YEARWEEK(fecha_pedido, 1) = YEARWEEK(CURDATE(), 1) GROUP BY dia";
$stmt_grafico = $conn->prepare($sql_grafico);
$stmt_grafico->bind_param("i", $id_restaurante_actual);
$stmt_grafico->execute();
$res_grafico = $stmt_grafico->get_result();
while ($row = $res_grafico->fetch_assoc()) {
    $ventas_dias[$row['dia']] = (float)$row['total'];
}
$stmt_grafico->close();


include '../includes/header.php';
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    #mapa-restaurante {
        height: 300px;
        width: 100%;
        border-radius: 10px;
        z-index: 1;
    }
</style>
<div class="hero-quickbite">
    <div class="container hero-text">
        <div class="dashboard-header d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 fw-bold">Panel de <?php echo htmlspecialchars($_SESSION['restaurante_nombre']); ?></h1>
                <p class="lead text-white-50 mb-0">Un resumen de la actividad de tu negocio.</p>
            </div>
            <a href="logout.php" class="btn btn-outline-danger mt-2 mt-md-0"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a>
        </div>
    </div>
</div>

<div class="main-content-overlay">
    <div class="container">

        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card dashboard-card h-100 shadow-sm border-0">
                    <div class="card-header bg-white border-bottom-0 pb-0 mt-2">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Ganancias de la Semana Actual</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="ventasChart" height="100"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card summary-card-gradient summary-card-2 shadow-sm h-100">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                        <div class="icon-circle mb-3" style="width: 70px; height: 70px; font-size: 2rem;">
                            <i class="bi bi-card-checklist"></i>
                        </div>
                        <h5 class="card-title text-white">Platos en Menú</h5>
                        <p class="display-4 fw-bold text-white mb-0"><?php echo $resultado_platos->num_rows; ?></p>
                        <span class="text-white-50">Total registrados activos</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-4" style="border-left: 5px solid #0d6efd !important;">
                    <div class="card-body text-center py-4">
                        <h6 class="text-uppercase text-muted fw-bold mb-2"><i class="bi bi-calendar-day me-1"></i> Hoy</h6>
                        <h2 class="text-primary fw-bold mb-2">S/ <?php echo number_format($stats_hoy['ganancias'], 2); ?></h2>
                        <span class="badge bg-primary rounded-pill px-3 py-2 fs-6"><?php echo $stats_hoy['total_ventas']; ?> Entregas</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-4" style="border-left: 5px solid #198754 !important;">
                    <div class="card-body text-center py-4">
                        <h6 class="text-uppercase text-muted fw-bold mb-2"><i class="bi bi-calendar-week me-1"></i> Esta Semana</h6>
                        <h2 class="text-success fw-bold mb-2">S/ <?php echo number_format($stats_semana['ganancias'], 2); ?></h2>
                        <span class="badge bg-success rounded-pill px-3 py-2 fs-6"><?php echo $stats_semana['total_ventas']; ?> Entregas</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-4" style="border-left: 5px solid #ffc107 !important;">
                    <div class="card-body text-center py-4">
                        <h6 class="text-uppercase text-muted fw-bold mb-2"><i class="bi bi-calendar-month me-1"></i> Este Mes</h6>
                        <h2 class="text-warning fw-bold mb-2" style="color: #d39e00 !important;">S/ <?php echo number_format($stats_mes['ganancias'], 2); ?></h2>
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fs-6"><?php echo $stats_mes['total_ventas']; ?> Entregas</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card dashboard-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Añadir Nuevo Plato al Menú</h5>
                    </div>
                    <div class="card-body">
                        <form action="../procesos/procesar_agregar_plato.php" method="POST"
                            enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3"><label for="nombre_plato" class="form-label">Nombre del
                                        Plato</label><input type="text" class="form-control" name="nombre_plato"
                                        required></div>
                                <div class="col-md-6 mb-3"><label for="precio" class="form-label">Precio
                                        (S/)</label><input type="number" step="0.10" class="form-control" name="precio"
                                        required></div>
                            </div>
                            <div class="mb-3"><label for="descripcion" class="form-label">Descripción</label><textarea
                                    class="form-control" name="descripcion" rows="2"></textarea></div>
                            <div class="mb-3"><label for="foto" class="form-label">Foto del Plato</label><input
                                    class="form-control" type="file" name="foto"></div>
                            <button type="submit" class="btn btn-primary w-100">Añadir Plato</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card dashboard-card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Configuración General</h5>
                    </div>
                    <div class="card-body">
                        <form action="../procesos/actualizar_horario.php" method="POST" class="mb-4">
    <h6 class="fw-bold"><i class="bi bi-clock-fill me-2"></i>Horario Comercial</h6>
    
    <div class="row align-items-end g-2 mb-2">
        <div class="col-12"><span class="badge bg-primary w-100 text-start">Lunes a Viernes</span></div>
        <div class="col">
            <label class="form-label small text-muted mb-0">Apertura</label>
            <input type="time" class="form-control form-control-sm" name="hora_apertura" value="<?php echo htmlspecialchars($restaurante_data['hora_apertura'] ?? ''); ?>">
        </div>
        <div class="col">
            <label class="form-label small text-muted mb-0">Cierre</label>
            <input type="time" class="form-control form-control-sm" name="hora_cierre" value="<?php echo htmlspecialchars($restaurante_data['hora_cierre'] ?? ''); ?>">
        </div>
    </div>
    
    <div class="row align-items-end g-2 mb-2">
        <div class="col-12"><span class="badge bg-success w-100 text-start">Sábados</span></div>
        <div class="col">
            <label class="form-label small text-muted mb-0">Apertura Sáb</label>
            <input type="time" class="form-control form-control-sm" name="hora_apertura_sab" value="<?php echo htmlspecialchars($restaurante_data['hora_apertura_sab'] ?? ''); ?>">
        </div>
        <div class="col">
            <label class="form-label small text-muted mb-0">Cierre Sáb</label>
            <input type="time" class="form-control form-control-sm" name="hora_cierre_sab" value="<?php echo htmlspecialchars($restaurante_data['hora_cierre_sab'] ?? ''); ?>">
        </div>
    </div>

    <div class="row align-items-end g-2 mb-3">
        <div class="col-12"><span class="badge bg-warning text-dark w-100 text-start">Domingos</span></div>
        <div class="col">
            <label class="form-label small text-muted mb-0">Apertura Dom</label>
            <input type="time" class="form-control form-control-sm" name="hora_apertura_dom" value="<?php echo htmlspecialchars($restaurante_data['hora_apertura_dom'] ?? ''); ?>">
        </div>
        <div class="col">
            <label class="form-label small text-muted mb-0">Cierre Dom</label>
            <input type="time" class="form-control form-control-sm" name="hora_cierre_dom" value="<?php echo htmlspecialchars($restaurante_data['hora_cierre_dom'] ?? ''); ?>">
        </div>
    </div>
    
    <button type="submit" class="btn btn-secondary w-100">Guardar Horarios</button>
</form>
                        <hr>
                        <form action="../procesos/actualizar_telefono.php" method="POST" class="mt-4">
                            <h6><i class="bi bi-whatsapp me-2"></i>Notificaciones</h6>
                            <div class="row align-items-end g-2">
                                <div class="col">
                                    <label class="form-label">Número de WhatsApp</label>
                                    <div class="input-group"><span class="input-group-text">+51</span><input type="tel"
                                            class="form-control" name="telefono"
                                            value="<?php echo htmlspecialchars($restaurante_data['telefono'] ?? ''); ?>"
                                            required></div>
                                </div>
                                <div class="col-auto"><button type="submit"
                                        class="btn btn-secondary w-100">Guardar</button></div>
                            </div>
                        </form>
                        <div class="card dashboard-card h-100 mt-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0 fw-bold" style="color: #8E44AD;"><i
                                        class="bi bi-qr-code-scan me-2"></i>Configuración de Yape/Plin</h5>
                            </div>
                            <div class="card-body">
                                <form action="../procesos/actualizar_yape.php" method="POST"
                                    enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label">Número asociado</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                            <input type="tel" class="form-control" name="yape_numero"
                                                value="<?php echo htmlspecialchars($restaurante_data['yape_numero'] ?? ''); ?>"
                                                placeholder="987654321">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Imagen del QR</label>
                                        <input type="file" class="form-control" name="yape_qr" accept="image/*">
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100"
                                        style="background: #8E44AD; border: none;">Guardar Datos</button>
                                </form>
                            </div>
                            
                        </div>
                        
                        <div class="card dashboard-card mt-4 border-warning">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge-fill me-2"></i>Solicitudes de Repartidores</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $sql_solicitudes = "SELECT ra.id AS id_afiliacion, r.nombre, r.telefono
                            FROM repartidor_afiliaciones ra
                            JOIN repartidores r ON ra.id_repartidor = r.id
                            WHERE ra.id_restaurante = ? AND ra.estado_afiliacion = 'pendiente'";

                                $stmt_sol = $conn->prepare($sql_solicitudes);
                                $stmt_sol->bind_param("i", $id_restaurante_actual);
                                $stmt_sol->execute();
                                $res_sol = $stmt_sol->get_result();

                                if ($res_sol->num_rows > 0):
                                ?>
                                    <div class="list-group list-group-flush">
                                        <?php while ($sol = $res_sol->fetch_assoc()): ?>
                                            <div class="list-group-item px-0">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($sol['nombre']); ?></h6>
                                                        <small class="text-muted">Tel: <?php echo htmlspecialchars($sol['telefono']); ?></small>
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <form action="../procesos/gestionar_afiliacion.php" method="POST" class="w-50">
                                                        <input type="hidden" name="id_afiliacion" value="<?php echo $sol['id_afiliacion']; ?>">
                                                        <input type="hidden" name="accion" value="aprobar">
                                                        <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-check-lg"></i> Aprobar</button>
                                                    </form>

                                                    <form action="../procesos/gestionar_afiliacion.php" method="POST" class="w-50">
                                                        <input type="hidden" name="id_afiliacion" value="<?php echo $sol['id_afiliacion']; ?>">
                                                        <input type="hidden" name="accion" value="rechazar">
                                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-x-lg"></i> Rechazar</button>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-3 text-muted">
                                        <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                        No tienes solicitudes nuevas.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
            <div class="mt-4 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold text-primary mb-0"><i class="bi bi-geo-alt-fill me-2"></i>Ubicación del Local
                    </h6>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-detectar-ubicacion">
                        <i class="bi bi-crosshair"></i> Usar mi GPS
                    </button>
                </div>
                <p class="small text-muted">Mueve el pin rojo a la ubicación exacta de tu restaurante. Esto es vital
                    para calcular el costo de envío.</p>

                <form action="../procesos/actualizar_ubicacion_restaurante.php" method="POST">
                    <div id="mapa-restaurante" class="mb-3 border bg-light"></div>

                    <div class="row g-2">
                        <div class="col">
                            <input type="text" class="form-control form-control-sm bg-light" name="latitud"
                                id="lat_rest"
                                value="<?php echo htmlspecialchars($restaurante_data['latitud'] ?? ''); ?>" readonly
                                placeholder="Latitud" required>
                        </div>
                        <div class="col">
                            <input type="text" class="form-control form-control-sm bg-light" name="longitud"
                                id="lon_rest"
                                value="<?php echo htmlspecialchars($restaurante_data['longitud'] ?? ''); ?>" readonly
                                placeholder="Longitud" required>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Guardar Ubicación</button>
                        </div>
                    </div>
                    <div id="gps-status" class="form-text mt-1"></div>
                </form>
            </div>
            <div class="col-lg-6 mt-4">
                <div class="card dashboard-card h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-tags-fill me-2 text-primary"></i>Categorías del
                            Restaurante</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Selecciona las categorías que mejor describen tu comida. Esto
                            ayuda a los clientes a encontrarte en los filtros.</p>

                        <form action="../procesos/actualizar_categorias.php" method="POST">
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <?php if ($res_all_cats->num_rows > 0): ?>
                                    <?php while ($cat = $res_all_cats->fetch_assoc()):
                                        // Verificamos si el restaurante ya tiene esta categoría
                                        $checked = in_array($cat['id'], $mis_categorias_ids) ? 'checked' : '';
                                        $clase_activa = in_array($cat['id'], $mis_categorias_ids) ? 'border-primary bg-primary text-white' : 'border-secondary text-muted';
                                    ?>
                                        <input type="checkbox" class="btn-check" id="cat_<?php echo $cat['id']; ?>"
                                            name="categorias[]" value="<?php echo $cat['id']; ?>" <?php echo $checked; ?>>

                                        <label class="btn btn-outline-primary btn-sm rounded-pill"
                                            for="cat_<?php echo $cat['id']; ?>">
                                            <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                                        </label>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="alert alert-warning w-100">
                                        No hay categorías registradas en el sistema. Contacta al administrador.
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Guardar Categorías</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-12">
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h5 class="mb-0">Imagen de Portada de tu Restaurante</h5>
                    </div>
                    <div class="card-body">
                        <form action="../procesos/actualizar_imagen_restaurante.php" method="POST"
                            enctype="multipart/form-data">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <p class="text-muted">Esta imagen aparecerá como fondo en la página principal. Sube
                                        una foto atractiva de tu local.</p>
                                    <div class="mb-3">
                                        <label for="foto_restaurante" class="form-label">Seleccionar nueva
                                            imagen:</label>
                                        <input class="form-control" type="file" name="foto_restaurante"
                                            id="foto_restaurante" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Subir y Guardar Imagen</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-1">
            <div class="col-lg-12">
                <div class="card dashboard-card">
                    <div class="card-header">
                        <h5 class="mb-0">Tu Menú Actual</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Foto</th>
                                        <th>Plato</th>
                                        <th>Descripción</th>
                                        <th class="text-end">Precio</th>
                                        <th class="text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($resultado_platos->num_rows > 0): ?>
                                        <?php
                                        // Aseguramos que el puntero del resultado esté al inicio
                                        $resultado_platos->data_seek(0);
                                        while ($plato = $resultado_platos->fetch_assoc()):
                                            // Determinamos el estilo si el plato está oculto
                                            $estilo_fila = ($plato['esta_visible'] == 0) ? 'style="opacity: 0.5; background-color: #f8f9fa;"' : '';
                                        ?>
                                            <tr <?php echo $estilo_fila; ?>>
                                                <td>
                                                    <img src="/cerrodeliveryv2/assets/img/platos/<?php echo htmlspecialchars($plato['foto_url']); ?>"
                                                        alt="<?php echo htmlspecialchars($plato['nombre_plato']); ?>"
                                                        style="width: 60px; height: 60px; object-fit: cover; border-radius: 0.5rem;">
                                                </td>
                                                <td class="fw-bold"><?php echo htmlspecialchars($plato['nombre_plato']); ?></td>
                                                <td class="small text-muted">
                                                    <?php echo htmlspecialchars($plato['descripcion']); ?>
                                                </td>
                                                <td class="text-end">S/ <?php echo number_format($plato['precio'], 2); ?></td>

                                                <td class="text-center">
                                                    <a href="editar_plato.php?id=<?php echo $plato['id']; ?>"
                                                        class="btn btn-outline-primary btn-sm me-1" title="Editar este plato">
                                                        <i class="bi bi-pencil-square"></i> Editar
                                                    </a>

                                                    <?php if ($plato['esta_visible'] == 1): ?>
                                                        <a href="../procesos/alternar_visibilidad_plato.php?id_plato=<?php echo $plato['id']; ?>"
                                                            class="btn btn-outline-warning btn-sm"
                                                            title="Ocultar este plato del menú público">
                                                            <i class="bi bi-eye-slash-fill"></i> Ocultar
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="../procesos/alternar_visibilidad_plato.php?id_plato=<?php echo $plato['id']; ?>"
                                                            class="btn btn-outline-success btn-sm"
                                                            title="Mostrar este plato en el menú público">
                                                            <i class="bi bi-eye-fill"></i> Mostrar
                                                        </a>
                                                    <?php endif; ?>
                                                </td>

                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center p-4 text-muted">Aún no has añadido ningún
                                                plato a tu menú.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // SCRIPT PARA INICIALIZAR EL GRÁFICO DE BARRAS
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('ventasChart').getContext('2d');
        const ventasDias = <?php echo json_encode($ventas_dias); ?>;
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
                datasets: [{
                    label: 'Ingresos Netos (S/)',
                    data: ventasDias,
                    backgroundColor: 'rgba(13, 110, 253, 0.6)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1,
                    borderRadius: 5,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'S/ ' + value;
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' S/ ' + context.parsed.y.toFixed(2);
                            }
                        }
                    },
                    legend: {
                        display: false 
                    }
                }
            }
        });
    });
</script>

<script>
    // SCRIPT DEL MAPA Y UBICACIÓN
    document.addEventListener('DOMContentLoaded', function() {
        const latInput = document.getElementById('lat_rest');
        const lonInput = document.getElementById('lon_rest');
        const gpsStatus = document.getElementById('gps-status');
        const btnGps = document.getElementById('btn-detectar-ubicacion');

        const defaultLat = -10.683;
        const defaultLng = -76.256;

        let currentLat = (latInput.value && !isNaN(latInput.value)) ? parseFloat(latInput.value) : defaultLat;
        let currentLng = (lonInput.value && !isNaN(lonInput.value)) ? parseFloat(lonInput.value) : defaultLng;

        if (document.getElementById('mapa-restaurante')) {
            const mapa = L.map('mapa-restaurante').setView([currentLat, currentLng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(mapa);

            let marcador = L.marker([currentLat, currentLng], {
                draggable: true
            }).addTo(mapa);

            marcador.on('dragend', function(e) {
                const position = e.target.getLatLng();
                latInput.value = position.lat.toFixed(6); 
                lonInput.value = position.lng.toFixed(6);
            });

            if (btnGps) {
                btnGps.addEventListener('click', function() {
                    if (navigator.geolocation) {
                        gpsStatus.innerHTML = '<span class="text-primary spinner-border spinner-border-sm"></span> Obteniendo ubicación...';

                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                const lat = position.coords.latitude;
                                const lng = position.coords.longitude;

                                mapa.setView([lat, lng], 18);
                                marcador.setLatLng([lat, lng]);

                                latInput.value = lat.toFixed(6);
                                lonInput.value = lng.toFixed(6);

                                gpsStatus.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check-circle"></i> Ubicación encontrada</span>';
                            },
                            function(error) {
                                console.error(error);
                                let msg = "Error desconocido";
                                if (error.code === 1) msg = "Permiso de ubicación denegado.";
                                if (error.code === 2) msg = "Ubicación no disponible (enciende tu GPS).";
                                if (error.code === 3) msg = "Tiempo de espera agotado.";

                                gpsStatus.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> ${msg}</span>`;
                            }, {
                                enableHighAccuracy: true,
                                timeout: 10000,
                                maximumAge: 0
                            }
                        );
                    } else {
                        gpsStatus.innerHTML = '<span class="text-danger">Tu navegador no soporta geolocalización.</span>';
                    }
                });
            }

            setTimeout(() => {
                mapa.invalidateSize();
            }, 500);
        }
    });
</script>

<?php
// Cierres de conexión
$stmt_platos->close();
$conn->close();
include '../includes/footer.php';
?>