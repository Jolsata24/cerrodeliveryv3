<?php
// --- Lógica PHP (Modificada) ---
session_start();
if (!isset($_SESSION['restaurante_id'])) {
    header('Location: ../login_restaurante.php');
    exit();
}
require_once '../includes/conexion.php';
$id_restaurante_actual = $_SESSION['restaurante_id'];

// Consultas para datos del restaurante y platos (SIN CAMBIOS)
// Consultas para datos del restaurante y platos (CORREGIDO)
// Agregamos latitud, longitud y datos de yape para que el dashboard no falle
$sql_restaurante = "SELECT hora_apertura, hora_cierre, telefono, yape_numero, yape_qr, latitud, longitud FROM restaurantes WHERE id = ?";
$stmt_restaurante = $conn->prepare($sql_restaurante);
$stmt_restaurante->bind_param("i", $id_restaurante_actual);
$stmt_restaurante->execute();
$restaurante_data = $stmt_restaurante->get_result()->fetch_assoc();
$stmt_restaurante->close();

// Obtener los platos del restaurante (SOLO LOS VISIBLES)
// CORRECTO para dashboard.php
$sql_platos = "SELECT * FROM menu_platos WHERE id_restaurante = ? ORDER BY id DESC";
$stmt_platos = $conn->prepare($sql_platos);
$stmt_platos->bind_param("i", $id_restaurante_actual);
$stmt_platos->execute();
$resultado_platos = $stmt_platos->get_result();

// ... (después de $resultado_platos = ... )

// 1. Obtener TODAS las categorías posibles (para el formulario)
$sql_all_cats = "SELECT * FROM categorias ORDER BY nombre_categoria ASC";
$res_all_cats = $conn->query($sql_all_cats);

// 2. Obtener las categorías QUE YA TIENE el restaurante (para marcarlas)
$sql_my_cats = "SELECT id_categoria FROM restaurante_categorias WHERE id_restaurante = ?";
$stmt_my_cats = $conn->prepare($sql_my_cats);
$stmt_my_cats->bind_param("i", $id_restaurante_actual);
$stmt_my_cats->execute();
$res_my_cats = $stmt_my_cats->get_result();

// Guardamos los IDs en un array simple para buscar fácil: [1, 5, 8]
$mis_categorias_ids = [];
while ($row = $res_my_cats->fetch_assoc()) {
    $mis_categorias_ids[] = $row['id_categoria'];
}
$stmt_my_cats->close();

// ... (sigue el include header.php)
// Consulta para pedidos pendientes (SIN CAMBIOS)
$sql_count = "SELECT COUNT(id) AS total_pendientes FROM pedidos WHERE id_restaurante = ? AND (estado_pedido = 'Pendiente' OR estado_pedido = 'En preparación')";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("i", $id_restaurante_actual);
$stmt_count->execute();
$row_count = $stmt_count->get_result()->fetch_assoc();
$total_pendientes = $row_count['total_pendientes'];



include '../includes/header.php';
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
            <a href="logout.php" class="btn btn-outline-danger mt-2 mt-md-0"><i
                    class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a>
        </div>
    </div>
</div>

<div class="main-content-overlay">
    <div class="container">

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card summary-card-gradient summary-card-1 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title text-white">Pedidos Activos</h5>
                            <p class="display-4 fw-bold text-white mb-0"><?php echo $total_pendientes; ?></p>
                            <a href="pedidos.php" class="stretched-link text-white-50">Gestionar pedidos</a>
                        </div>
                        <div class="icon-circle">
                            <i class="bi bi-receipt-cutoff"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card summary-card-gradient summary-card-2 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title text-white">Platos en Menú</h5>
                            <p class="display-4 fw-bold text-white mb-0"><?php echo $resultado_platos->num_rows; ?></p>
                            <span class="text-white-50">Total registrados</span>
                        </div>
                        <div class="icon-circle">
                            <i class="bi bi-card-checklist"></i>
                        </div>
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
                    <div class="card dashboard-card mt-4 border-warning">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge-fill me-2"></i>Solicitudes de Repartidores</h5>
    </div>
    <div class="card-body">
        <?php
        // Consulta para buscar solicitudes pendientes
        // Nota: Nos aseguramos de usar columnas que SÍ existen en tu BD (nombre, telefono)
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
                    <div class="card-body">
                        <form action="../procesos/actualizar_horario.php" method="POST" class="mb-4">
                            <h6><i class="bi bi-clock-fill me-2"></i>Horario Comercial</h6>
                            <div class="row align-items-end g-2">
                                <div class="col"><label class="form-label">Apertura</label><input type="time"
                                        class="form-control" name="hora_apertura"
                                        value="<?php echo htmlspecialchars($restaurante_data['hora_apertura'] ?? ''); ?>">
                                </div>
                                <div class="col"><label class="form-label">Cierre</label><input type="time"
                                        class="form-control" name="hora_cierre"
                                        value="<?php echo htmlspecialchars($restaurante_data['hora_cierre'] ?? ''); ?>">
                                </div>
                                <div class="col-auto"><button type="submit"
                                        class="btn btn-secondary w-100">Guardar</button></div>
                            </div>
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
                                $id_restaurante = $_SESSION['restaurante_id'];

                                // CORRECCIÓN: Quitamos 'fecha_solicitud' y 'apellido' que no existen en tu BD
                                $sql_solicitudes = "SELECT ra.id AS id_afiliacion, r.nombre, r.telefono
                            FROM repartidor_afiliaciones ra
                            JOIN repartidores r ON ra.id_repartidor = r.id
                            WHERE ra.id_restaurante = ? AND ra.estado_afiliacion = 'pendiente'";

                                $stmt_sol = $conn->prepare($sql_solicitudes);
                                $stmt_sol->bind_param("i", $id_restaurante);
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
            <div class="col-lg-6">
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
    document.addEventListener('DOMContentLoaded', function() {
        // ==========================================
        // 1. VARIABLES Y CONFIGURACIÓN INICIAL
        // ==========================================
        const selectPago = document.getElementById('metodo_pago');
        const containerYape = document.getElementById('info-yape-container');
        const displayYapeNum = document.getElementById('yape-numero-display');
        const displayYapeQR = document.getElementById('yape-qr-img-placeholder');
        const btnCopiar = document.getElementById('btn-copiar-yape');
        const msgCopia = document.getElementById('mensaje-copia');
        const divVuelto = document.getElementById('div-vuelto');
        const inputVuelto = document.getElementById('monto_pagar');

        // Variables para el mapa y envío
        const defaultLat = -10.683; // Cerro de Pasco
        const defaultLng = -76.256;
        let userLat = defaultLat;
        let userLng = defaultLng;
        let datosRestaurante = {
            lat: null,
            lon: null,
            yapeNumero: '',
            yapeQR: ''
        };

        // Inicializar Mapa
        const mapa = L.map('mapa-checkout').setView([defaultLat, defaultLng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(mapa);

        // Marcador movible
        let marcador = L.marker([defaultLat, defaultLng], {
            draggable: true
        }).addTo(mapa);

        // ==========================================
        // 2. OBTENER DATOS DEL RESTAURANTE
        // ==========================================
        // Recuperamos el ID del restaurante guardado en el carrito
        const carritoKey = `carritoData_${CLIENTE_ID}`; // CLIENTE_ID viene de PHP
        const carritoData = JSON.parse(sessionStorage.getItem(carritoKey));

        if (carritoData && carritoData.restauranteId) {
            // Rellenar el input hidden del ID restaurante
            document.getElementById('id_restaurante').value = carritoData.restauranteId;
            document.getElementById('carrito_data').value = JSON.stringify(carritoData.items);

            // Pedir datos al servidor
            fetch(`procesos/obtener_datos_restaurante.php?id_restaurante=${carritoData.restauranteId}`)
                .then(response => response.json())
                .then(resp => {
                    if (resp.status === 'success') {
                        datosRestaurante.lat = parseFloat(resp.data.latitud);
                        datosRestaurante.lon = parseFloat(resp.data.longitud);
                        datosRestaurante.yapeNumero = resp.data.yape_numero;
                        datosRestaurante.yapeQR = resp.data.yape_qr;

                        // Una vez tenemos los datos, recalculamos por si acaso
                        actualizarTotalesEnvio();
                    }
                })
                .catch(err => console.error("Error cargando datos restaurante:", err));
        }

        // ==========================================
        // 3. LÓGICA DE CÁLCULO DE ENVÍO
        // ==========================================
        function calcularCosto(clienteLat, clienteLon) {
            // Si el restaurante no tiene mapa configurado, cobramos tarifa base
            if (!datosRestaurante.lat || !datosRestaurante.lon) return 5.00;

            const R = 6371; // Radio tierra km
            const dLat = (clienteLat - datosRestaurante.lat) * Math.PI / 180;
            const dLon = (clienteLon - datosRestaurante.lon) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(datosRestaurante.lat * Math.PI / 180) * Math.cos(clienteLat * Math.PI / 180) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            const distancia = R * c;

            // TARIFA: Base S/5.00 por 1.5km, luego S/2.00 por cada km extra
            let costo = 5.00;
            if (distancia > 1.5) {
                costo += (distancia - 1.5) * 2.00;
            }
            return Math.round(costo * 10) / 10; // Redondear a 1 decimal
        }

        function actualizarTotalesEnvio() {
            const costoEnvio = calcularCosto(userLat, userLng);

            // 1. Actualizar inputs ocultos para que se guarden en BD
            document.getElementById('latitud').value = userLat;
            document.getElementById('longitud').value = userLng;

            // 2. Calcular subtotal de productos desde el carrito guardado
            let subtotalProductos = 0;
            if (carritoData && carritoData.items) {
                carritoData.items.forEach(item => {
                    subtotalProductos += (item.precio * item.cantidad);
                });
            }
            const totalPagar = subtotalProductos + costoEnvio;

            // 3. ACTUALIZAR LA TABLA VISUALMENTE
            const tfoot = document.querySelector('.summary-table tfoot');
            let rowEnvio = document.getElementById('row-costo-envio');

            // Si la fila de envío no existe, la creamos
            if (!rowEnvio && tfoot) {
                rowEnvio = document.createElement('tr');
                rowEnvio.id = 'row-costo-envio';
                // Insertamos antes de la última fila (que es el Total)
                const filaTotal = tfoot.lastElementChild;
                rowEnvio.innerHTML = `
                <td colspan="2" class="text-end text-muted small pe-4">Costo de Envío (Distancia)</td>
                <td class="text-end text-muted small pe-4" id="valor-envio"></td>
                <td></td>
            `;
                tfoot.insertBefore(rowEnvio, filaTotal);
            }

            // Actualizar textos
            if (document.getElementById('valor-envio')) {
                document.getElementById('valor-envio').textContent = `S/ ${costoEnvio.toFixed(2)}`;
            }

            // Actualizar el Total Grande
            const celdaTotal = document.querySelector('.total-row .h5');
            if (celdaTotal) {
                celdaTotal.textContent = `S/ ${totalPagar.toFixed(2)}`;
            }
        }

        // ==========================================
        // 4. EVENTOS DEL MAPA Y GPS
        // ==========================================

        // A) Si muevo el pin manualmente
        marcador.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            userLat = pos.lat;
            userLng = pos.lng;
            actualizarTotalesEnvio(); // <--- IMPORTANTE: Recalcula al soltar
        });

        // B) Si uso el botón de GPS
        const btnGps = document.getElementById('usar-gps-btn');
        const gpsStatus = document.getElementById('gps-status');
        const dirInput = document.getElementById('direccion_pedido');

        if (btnGps) {
            btnGps.addEventListener('click', function() {
                if (navigator.geolocation) {
                    gpsStatus.innerHTML = '<span class="text-primary spinner-border spinner-border-sm"></span> Buscando...';

                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            userLat = position.coords.latitude;
                            userLng = position.coords.longitude;

                            // 1. Mover mapa y marcador
                            mapa.setView([userLat, userLng], 16);
                            marcador.setLatLng([userLat, userLng]);

                            // 2. Rellenar campo de texto (opcional)
                            dirInput.value = `Ubicación GPS (Lat: ${userLat.toFixed(4)}, Lon: ${userLng.toFixed(4)}) - Completa detalles...`;

                            // 3. RECALCULAR PRECIO
                            actualizarTotalesEnvio();

                            gpsStatus.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check-circle"></i> Ubicación y precio actualizados</span>';
                        },
                        function(error) {
                            console.error(error);
                            gpsStatus.innerHTML = '<span class="text-danger">Error: No se pudo obtener ubicación.</span>';
                        }, {
                            enableHighAccuracy: true
                        }
                    );
                } else {
                    gpsStatus.innerHTML = '<span class="text-danger">Tu navegador no soporta GPS.</span>';
                }
            });
        }

        // ==========================================
        // 5. LÓGICA DE YAPE Y VUELTO (Visualización)
        // ==========================================
        selectPago.addEventListener('change', function() {
            containerYape.style.display = 'none';
            divVuelto.style.display = 'none';
            inputVuelto.removeAttribute('required');

            if (this.value === 'yape') {
                containerYape.style.display = 'block';
                displayYapeNum.textContent = datosRestaurante.yapeNumero || "No registrado";
                if (datosRestaurante.yapeQR) {
                    displayYapeQR.innerHTML = `<img src="assets/img/qr/${datosRestaurante.yapeQR}" class="img-fluid rounded border" style="max-width: 200px;">`;
                } else {
                    displayYapeQR.innerHTML = '<span class="text-muted small">Sin código QR</span>';
                }
            } else if (this.value === 'efectivo') {
                divVuelto.style.display = 'block';
                inputVuelto.setAttribute('required', 'true');
            }
        });

        // Copiar número Yape
        if (btnCopiar) {
            btnCopiar.addEventListener('click', function() {
                const num = displayYapeNum.textContent;
                if (num && num.length > 5) {
                    navigator.clipboard.writeText(num);
                    if (msgCopia) {
                        msgCopia.style.display = 'inline-block';
                        setTimeout(() => msgCopia.style.display = 'none', 2000);
                    }
                }
            });
        }

        // Ajuste final visual del mapa
        setTimeout(() => {
            mapa.invalidateSize();
        }, 500);
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // 1. OBTENER COORDENADAS INICIALES
        // Si la BD ya tiene lat/lon, las usamos. Si no, usamos las de Cerro de Pasco por defecto.
        const latInput = document.getElementById('lat_rest');
        const lonInput = document.getElementById('lon_rest');
        const gpsStatus = document.getElementById('gps-status');
        const btnGps = document.getElementById('btn-detectar-ubicacion');

        // Coordenadas por defecto (Cerro de Pasco)
        const defaultLat = -10.683;
        const defaultLng = -76.256;

        // Verificar si los inputs tienen valor numérico válido
        let currentLat = (latInput.value && !isNaN(latInput.value)) ? parseFloat(latInput.value) : defaultLat;
        let currentLng = (lonInput.value && !isNaN(lonInput.value)) ? parseFloat(lonInput.value) : defaultLng;

        // 2. INICIALIZAR MAPA
        // Usamos el ID correcto del dashboard: 'mapa-restaurante'
        // Aseguramos que el div exista para evitar errores
        if (document.getElementById('mapa-restaurante')) {
            const mapa = L.map('mapa-restaurante').setView([currentLat, currentLng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(mapa);

            // 3. MARCADOR
            let marcador = L.marker([currentLat, currentLng], {
                draggable: true
            }).addTo(mapa);

            // Evento: Al mover el marcador manualmente (CORREGIDO 'marker' por 'e.target')
            marcador.on('dragend', function(e) {
                const position = e.target.getLatLng();
                latInput.value = position.lat.toFixed(6); // Redondear para que se vea ordenado
                lonInput.value = position.lng.toFixed(6);
            });

            // 4. BOTÓN "USAR MI GPS"
            if (btnGps) {
                btnGps.addEventListener('click', function() {
                    if (navigator.geolocation) {
                        gpsStatus.innerHTML = '<span class="text-primary spinner-border spinner-border-sm"></span> Obteniendo ubicación...';

                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                const lat = position.coords.latitude;
                                const lng = position.coords.longitude;

                                // Actualizar mapa y marcador
                                mapa.setView([lat, lng], 18);
                                marcador.setLatLng([lat, lng]);

                                // Actualizar inputs
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

            // Ajuste visual del mapa al cargar (para que no se vea gris)
            setTimeout(() => {
                mapa.invalidateSize();
            }, 500);
        }
    });
</script>
<?php
// --- Cierres de conexión (SIN CAMBIOS) ---
$stmt_platos->close();
$stmt_count->close();
$conn->close();
include '../includes/footer.php';
?>