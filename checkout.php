<?php
session_start();
require_once 'includes/conexion.php'; 
// --- OBTENER CONFIGURACIÓN DE PAGO CENTRALIZADO ---
$yape_numero = '969704480'; // Valor por defecto
$yape_titular = 'Cerro Delivery SAC'; // Valor por defecto
$yape_qr = 'assets/img/qr/qr_1.png'; // Imagen por defecto si falla la BD

try {
    // AÑADIDO: Ahora también buscamos 'yape_qr'
    $sql_config = "SELECT nombre_config, valor_config FROM configuracion WHERE nombre_config IN ('yape_numero', 'yape_titular', 'yape_qr')";
    $res_config = $conn->query($sql_config);

    if ($res_config && $res_config->num_rows > 0) {
        $config_yape = [];
        while($row = $res_config->fetch_assoc()){
            $config_yape[$row['nombre_config']] = $row['valor_config'];
        }
        
        if(!empty($config_yape['yape_numero'])) $yape_numero = $config_yape['yape_numero'];
        if(!empty($config_yape['yape_titular'])) $yape_titular = $config_yape['yape_titular'];
        // AÑADIDO: Reemplazamos la imagen si se encontró en la BD
        if(!empty($config_yape['yape_qr'])) $yape_qr = $config_yape['yape_qr'];
    }
} catch (Exception $e) {
    // Si hay error, usará los valores por defecto
}
// =========================================================================
// ⏰ VALIDACIÓN DE HORARIO DE ATENCIÓN (CHECKOUT)
// =========================================================================
date_default_timezone_set('America/Lima'); 
$hora_actual = date('H:i');

$sql_horario = "SELECT nombre_config, valor_config FROM configuracion WHERE nombre_config IN ('horario_apertura', 'horario_cierre')";
$res_horario = $conn->query($sql_horario);
$apertura = '18:30'; $cierre = '23:00'; 

if ($res_horario && $res_horario->num_rows > 0) {
    while($row = $res_horario->fetch_assoc()) {
        if($row['nombre_config'] == 'horario_apertura') $apertura = $row['valor_config'];
        if($row['nombre_config'] == 'horario_cierre') $cierre = $row['valor_config'];
    }
}

$fuera_de_horario = false;
if ($apertura < $cierre) {
    if ($hora_actual < $apertura || $hora_actual > $cierre) $fuera_de_horario = true;
} else {
    if ($hora_actual < $apertura && $hora_actual > $cierre) $fuera_de_horario = true;
}

if ($fuera_de_horario) {
    // Aquí redirigimos a index.php sin los "../" porque estamos en la carpeta principal
    header("Location: index.php?error=horario_cerrado");
    exit();
}
// =========================================================================

// Seguridad: Verificar sesión
if (!isset($_SESSION['cliente_id'])) {
    header('Location: login_cliente.php');
    exit();
}

// 1. OBTENER DATOS DEL CLIENTE
$id_cliente = $_SESSION['cliente_id'];
$sql_c = "SELECT telefono FROM usuarios_clientes WHERE id = ?";
$stmt_c = $conn->prepare($sql_c);
$stmt_c->bind_param("i", $id_cliente);
$stmt_c->execute();
$res_c = $stmt_c->get_result();
$datos_c = $res_c->fetch_assoc();
$telefono_cliente = $datos_c['telefono'] ?? ''; 

// 2. CONSULTAR CONFIGURACIÓN (MODO LLUVIA)
$modo_lluvia_activo = '0';
$monto_extra_lluvia = 0.00;

// CORRECCIÓN: Usamos los nuevos nombres de las columnas 'nombre_config' y 'valor_config'
$sql_conf = "SELECT nombre_config, valor_config FROM configuracion WHERE nombre_config IN ('modo_lluvia', 'monto_recargo_lluvia')";
$res_conf = $conn->query($sql_conf);

if($res_conf) {
    while($row = $res_conf->fetch_assoc()) {
        if($row['nombre_config'] == 'modo_lluvia') $modo_lluvia_activo = $row['valor_config'];
        if($row['nombre_config'] == 'monto_recargo_lluvia') $monto_extra_lluvia = floatval($row['valor_config']);
    }
}

include 'includes/header.php';
?>
<body>
    <?php if (isset($_GET['error']) && $_GET['error'] == 'horario_cerrado'): ?>
    <div class="container mt-4 position-relative" style="z-index: 9999;">
        <div class="alert alert-warning alert-dismissible fade show shadow border-warning" role="alert">
            <i class="bi bi-clock-history fs-4 me-2 text-danger"></i>
            <strong>¡Lo sentimos, estamos fuera del horario de atención!</strong><br> 
            Nuestro horario de delivery es de <strong>6:30 PM a 11:00 PM</strong>. ¡Te esperamos más tarde para calmar esos antojos!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>
<div class="hero-quickbite">
    <div class="container hero-text text-center">
        <h1 class="display-4 fw-bold">Finalizar Pedido</h1>
        <p class="lead text-white-50">Confirma tu ubicación y método de pago.</p>
    </div>
</div>

<div class="main-content-overlay">
    <div class="container">

        <div class="mb-4">
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Seguir Comprando
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card checkout-card mb-3">
                    <div class="card-header">
                        <h4 class="mb-0">🛒 Tu Carrito</h4>
                    </div>
                    <div id="resumen-carrito" class="card-body p-0">
                        <div class="p-4 text-center">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                
                <?php if($modo_lluvia_activo == '1'): ?>
                <div class="alert alert-warning d-flex align-items-center shadow-sm border-warning mb-4" role="alert">
                    <div class="me-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-cloud-lightning-rain-fill" viewBox="0 0 16 16">
                          <path d="M2.658 11.026a.5.5 0 0 1 .316.632l-.5 1.5a.5.5 0 1 1-.948-.316l.5-1.5a.5.5 0 0 1 .632-.316zm9.5 0a.5.5 0 0 1 .316.632l-.5 1.5a.5.5 0 1 1-.948-.316l.5-1.5a.5.5 0 0 1 .632-.316zm-7.5 1.5a.5.5 0 0 1 .316.632l-.5 1.5a.5.5 0 1 1-.948-.316l.5-1.5a.5.5 0 0 1 .632-.316zm9.5 0a.5.5 0 0 1 .316.632l-.5 1.5a.5.5 0 1 1-.948-.316l.5-1.5a.5.5 0 0 1 .632-.316zm-.753-8.499a5.001 5.001 0 0 0-9.499-1.004A3.5 3.5 0 1 0 3.5 10H13a3 3 0 0 0 .405-5.973zM8.5 1a4 4 0 0 1 3.976 3.555.5.5 0 0 0 .5.445H13a2 2 0 0 1 0 4H3.5a2.5 2.5 0 1 1 .605-4.926.5.5 0 0 0 .596-.329A4.002 4.002 0 0 1 8.5 1zM7.053 11.276A.5.5 0 0 1 7.5 11h1a.5.5 0 0 1 .474.658l-.28.842-.28.842a.5.5 0 0 1-.948 0l-.28-.842-.28-.842a.5.5 0 0 1 .474-.658z"/>
                        </svg>
                    </div>
                    <div>
                        <h6 class="alert-heading fw-bold mb-0 text-dark">¡Clima Complicado!</h6>
                        <p class="mb-0 small text-dark lh-sm">Tarifa dinámica activa por lluvias (+ S/ <?php echo number_format($monto_extra_lluvia, 2); ?>).</p>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card checkout-card">
                    <div class="card-header">
                        <h4 class="mb-0">📝 Datos de Entrega</h4>
                    </div>
                    <div class="card-body">
                        <p class="lead fs-6">Cliente: <strong><?php echo htmlspecialchars($_SESSION['cliente_nombre']); ?></strong></p>
                        
                        <form action="procesos/procesar_pedido.php" method="POST" id="checkout-form" enctype="multipart/form-data">
                            <input type="hidden" name="costo_envio" id="input_costo_envio">
                            <input type="hidden" name="carrito" id="carrito_data">
                            <input type="hidden" name="restaurante_id" id="id_restaurante"> 
                            <input type="hidden" name="latitud" id="latitud">
                            <input type="hidden" name="longitud" id="longitud">
                            <input type="hidden" name="total" id="input_total">
                            <input type="hidden" name="telefono" value="<?php echo htmlspecialchars($telefono_cliente); ?>">
                            <div class="mb-4">
    <label class="form-label fw-bold"><i class="bi bi-geo-alt-fill me-2"></i>Ubicación Exacta</label>
    
    <div class="alert alert-info py-2 small mb-2 text-center" style="border-radius: 10px;">
        <i class="bi bi-info-circle-fill me-1"></i> <b>¡Interactivo!</b> Mueve el pin en el mapa para ubicar tu casa.
    </div>

    <div id="mapa-checkout" style="height: 250px; width: 100%; border-radius: 10px; margin-bottom: 10px;" class="border"></div>
    <div id="alerta-zona" class="form-text mb-2 fw-bold"></div>
    
    <div class="d-grid mb-3">
        <button type="button" class="btn btn-outline-primary btn-sm" id="usar-gps-btn">
            <i class="bi bi-crosshair me-1"></i> Usar mi ubicación GPS
        </button>
        <div id="gps-status" class="form-text text-center"></div>
    </div>
    
    <div class="row g-2">
        <div class="col-12">
            <label for="direccion" class="form-label small fw-bold text-dark mb-1">Dirección exacta</label>
            <textarea class="form-control" id="direccion" name="direccion" rows="2" required placeholder="Mueve el pin en el mapa o escribe tu calle aquí..."></textarea>
        </div>
        <div class="col-12">
            <label for="referencia" class="form-label small fw-bold text-dark mb-1">Referencia (Opcional)</label>
            <input type="text" class="form-control" id="referencia" name="referencia" placeholder="Ej: Portón negro, frente a la cancha...">
        </div>
    </div>
</div>

<div class="mb-4">
    <label class="form-label fw-bold"><i class="bi bi-wallet2 me-2"></i>Método de Pago</label>
    <select class="form-select mb-3" id="metodo_pago" name="metodo_pago" required>
        <option value="" selected disabled>Selecciona cómo pagar</option>
        <option value="yape">Yape / Plin</option>
        <option value="efectivo" disabled class="bg-light text-muted">Efectivo (Temporalmente Inactivo)</option>
    </select>

                                <div id="seccion-yape" class="mt-4 p-3 border rounded bg-light" style="border-left: 5px solid #732290 !important;">
    <div class="d-flex align-items-center mb-3">
        <div class="bg-white p-2 rounded shadow-sm me-3">
            <img src="assets/img/logo.png" width="30" alt="Logo">
        </div>
        <div>
            <h6 class="mb-0 fw-bold text-dark">Pago Único Cerro Delivery</h6>
            <small class="text-muted">Escanea o copia el número abajo</small>
        </div>
    </div>

    <div class="text-center">
        <img src="<?php echo htmlspecialchars($yape_qr); ?>" style="max-width: 160px; border: 4px solid #732290;" class="img-thumbnail shadow-sm mb-3" alt="QR Yape Cerro Delivery">
        
        <div class="bg-white p-2 rounded mb-2 border">
            <span class="d-block small text-muted">Titular de la cuenta:</span>
            <span class="fw-bold text-dark text-uppercase"><?php echo htmlspecialchars($yape_titular); ?></span>
        </div>

        <div class="input-group mb-3 shadow-sm">
            <span class="input-group-text bg-white text-muted small">Número:</span>
            <input type="text" class="form-control fw-bold text-center bg-white" id="numero-yape" value="<?php echo htmlspecialchars($yape_numero); ?>" readonly>
            <button class="btn btn-primary" type="button" onclick="copiarNumero()">
                <i class="bi bi-clipboard-check me-1"></i> Copiar
            </button>
        </div>

        <p class="small text-muted mb-2">
            <i class="bi bi-camera-fill me-1"></i> Sube tu comprobante de pago:
        </p>
        <input type="file" name="evidencia_yape" class="form-control border-primary" id="evidencia_yape" accept="image/*">
    </div>
</div>

                                <div id="div-vuelto" style="display: none;">
                                    <label for="monto_pagar" class="form-label small">¿Con cuánto pagarás?</label>
                                    <div class="input-group">
                                        <span class="input-group-text">S/</span>
                                        <input type="number" class="form-control" id="monto_pagar" name="monto_pagar" placeholder="Ej: 50.00" step="0.10">
                                    </div>
                                </div>
                            </div>

                            <div class="form-check mb-4 p-3 border rounded bg-white shadow-sm">
                                <input class="form-check-input" type="checkbox" id="check_puerta" style="transform: scale(1.2);">
                                <label class="form-check-label small text-dark lh-sm ms-2" for="check_puerta">
                                    <strong>⚠️ Política de Entrega Segura:</strong><br>
                                    Acepto recibir el pedido en la <u>puerta principal de la calle</u>. Entiendo que el repartidor no sube a pisos ni entra a condominios por seguridad.
                                </label>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg btn-confirm-order" disabled>Cargando mapa...</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalBoleta" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-primary text-white" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold">Confirma tu Pedido</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="mb-3 p-3 bg-white rounded shadow-sm border">
                    <h6 class="fw-bold text-primary mb-2">📍 Destino</h6>
                    <p class="mb-0 fw-bold text-dark" id="boleta-direccion">...</p>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <ul class="list-group list-group-flush mb-3" id="boleta-items"></ul>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal Productos:</span>
                            <span class="fw-bold" id="boleta-subtotal">S/ 0.00</span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2 align-items-center">
                            <span class="text-muted">Costo de Envío:</span>
                            <div class="text-end">
                                <span class="fw-bold text-primary" id="boleta-envio">S/ 0.00</span>
                                <div id="boleta-badges" class="small"></div>
                            </div>
                        </div>

                        <hr class="my-2 dashed">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 fw-bold text-dark mb-0">TOTAL:</span>
                            <span class="h4 fw-bold text-success mb-0" id="boleta-total">S/ 0.00</span>
                        </div>
                        <div class="mt-3 text-center">
                            <span class="badge bg-secondary" id="boleta-metodo">...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light justify-content-center pb-4">
                <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Volver</button>
                <button type="button" class="btn btn-success px-5 rounded-pill fw-bold shadow" id="btn-enviar-final">
                    ¡Confirmar Pedido!
                </button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // =========================================================
    // 1. CONFIGURACIÓN Y VARIABLES GLOBALES
    // =========================================================
    const CLIENTE_ID = <?php echo $_SESSION['cliente_id']; ?>;
    
    // --- VARIABLES DE CLIMA ---
    const ES_MODO_LLUVIA = <?php echo ($modo_lluvia_activo == '1') ? 'true' : 'false'; ?>;
    const RECARGO_LLUVIA = <?php echo floatval($monto_extra_lluvia); ?>;

    // --- VARIABLES DE VOLUMEN (MOCHILA) ---
    const CAPACIDAD_MOCHILA = 10;      // Puntos máximos
    const RECARGO_CARGA_PESADA = 4.00; // Recargo si se pasa

    // --- ZONA DE REPARTO ---
    const zonaReparto = [
    [-10.67445, -76.24746],
    [-10.67126, -76.24692],
    [-10.66983, -76.24769],
    [-10.66839, -76.24797],
    [-10.66692, -76.24842],
    [-10.66485, -76.24937],
    [-10.66306, -76.24913],
    [-10.66106, -76.24906],
    [-10.65722, -76.25087],
    [-10.65804, -76.25286],
    [-10.66065, -76.25518],
    [-10.66171, -76.25735],
    [-10.66388, -76.25799],
    [-10.66555, -76.26001],
    [-10.66555, -76.26263],
    [-10.66711, -76.26284],
    [-10.66711, -76.26374],
    [-10.66623, -76.26451],
    [-10.66517, -76.26589],
    [-10.66623, -76.26713],
    [-10.66711, -76.26765],
    [-10.66825, -76.26786],
    [-10.6693, -76.26812],
    [-10.66998, -76.26872],
    [-10.67059, -76.26924],
    [-10.66987, -76.26954],
    [-10.66865, -76.26941],
    [-10.66795, -76.27018],
    [-10.66744, -76.27061],
    [-10.6682, -76.27207],
    [-10.66907, -76.27363],
    [-10.66997, -76.27355],
    [-10.67099, -76.27396],
    [-10.67202, -76.27402],
    [-10.67259, -76.27348],
    [-10.6728, -76.27267],
    [-10.67366, -76.27308],
    [-10.6743, -76.2728],
    [-10.67396, -76.2716],
    [-10.67341, -76.27102],
    [-10.6728, -76.27067],
    [-10.67225, -76.26872],
    [-10.67314, -76.26724],
    [-10.67426, -76.26692],
    [-10.67255, -76.2661],
    [-10.6707, -76.26563],
    [-10.66947, -76.26537],
    [-10.66855, -76.26426],
    [-10.66804, -76.26288],
    [-10.66791, -76.26117],
    [-10.66901, -76.25984],
    [-10.66897, -76.25833],
    [-10.66914, -76.25743],
    [-10.67076, -76.25763],
    [-10.67189, -76.25709],
    [-10.67316, -76.25619],
    [-10.67409, -76.25542],
    [-10.67529, -76.25441],
    [-10.67609, -76.25389],
    [-10.67685, -76.25366],
    [-10.67793, -76.25394],
    [-10.67825, -76.25432],
    [-10.67831, -76.25476],
    [-10.67834, -76.25534],
    [-10.67846, -76.25597],
    [-10.67884, -76.2563],
    [-10.67943, -76.25621],
    [-10.67999, -76.25609],
    [-10.68054, -76.25634],
    [-10.68113, -76.25678],
    [-10.68176, -76.25728],
    [-10.68258, -76.25758],
    [-10.68284, -76.25777],
    [-10.68352, -76.25824],
    [-10.6839, -76.25846],
    [-10.6845, -76.26013],
    [-10.68558, -76.26083],
    [-10.68575, -76.26239],
    [-10.68628, -76.2632],
    [-10.68648, -76.26389],
    [-10.68664, -76.26449],
    [-10.68748, -76.26472],
    [-10.68825, -76.26468],
    [-10.6887, -76.26462],
    [-10.68912, -76.26365],
    [-10.68954, -76.26276],
    [-10.69003, -76.26152],
    [-10.69006, -76.26037],
    [-10.68954, -76.25943],
    [-10.6901, -76.25821],
    [-10.69115, -76.25649],
    [-10.69237, -76.25524],
    [-10.69199, -76.25314],
    [-10.6906, -76.25228],
    [-10.69064, -76.25134],
    [-10.69106, -76.24988],
    [-10.69064, -76.24898],
    [-10.68984, -76.24834],
    [-10.68883, -76.24846],
    [-10.68824, -76.24812],
    [-10.68782, -76.24799],
    [-10.6882, -76.24718],
    [-10.68858, -76.24658],
    [-10.68921, -76.24709],
    [-10.69005, -76.24739],
    [-10.69022, -76.24649],
    [-10.68959, -76.24537],
    [-10.68919, -76.24474],
    [-10.68961, -76.24424],
    [-10.69031, -76.24341],
    [-10.69098, -76.24379],
    [-10.69171, -76.24403],
    [-10.69212, -76.24327],
    [-10.69193, -76.2428],
    [-10.69166, -76.2424],
    [-10.69109, -76.24216],
    [-10.69061, -76.24193],
    [-10.69019, -76.24169],
    [-10.6898, -76.24156],
    [-10.68951, -76.24136],
    [-10.68869, -76.24107],
    [-10.68772, -76.24073],
    [-10.68722, -76.24048],
    [-10.68644, -76.24025],
    [-10.68459, -76.24239],
    [-10.684, -76.24306],
    [-10.68248, -76.24331],
    [-10.6809, -76.2449],
    [-10.68048, -76.24662],
    [-10.67955, -76.24782],
    [-10.67888, -76.24851],
    [-10.67803, -76.24851],
    [-10.67703, -76.24799],
    [-10.676, -76.24757],
    [-10.67548, -76.24736],
];

    // DOM
    const resumenDiv = document.getElementById('resumen-carrito');
    const carritoDataInput = document.getElementById('carrito_data');
    const restauranteIdInput = document.getElementById('id_restaurante');
    const totalInput = document.getElementById('input_total');
    const checkoutForm = document.getElementById('checkout-form');
    
    const selectPago = document.getElementById('metodo_pago');
    const containerYape = document.getElementById('info-yape-container');
    const displayYapeNum = document.getElementById('yape-numero-display');
    const displayYapeQR = document.getElementById('yape-qr-img-placeholder');
    const btnCopiar = document.getElementById('btn-copiar-yape');
    const divVuelto = document.getElementById('div-vuelto');
    const inputVuelto = document.getElementById('monto_pagar');
    
    const modalBoleta = new bootstrap.Modal(document.getElementById('modalBoleta'));
    const btnEnviarFinal = document.getElementById('btn-enviar-final');
    const btnConfirmarInicial = document.querySelector('.btn-confirm-order');
    const alertaZona = document.getElementById('alerta-zona');
    const inputYapeFile = document.getElementById('comprobante_yape');
    const checkPuerta = document.getElementById('check_puerta'); // Referencia al checkbox puerta

    // Estado
    const carritoKey = `carritoData_${CLIENTE_ID}`;
    let carritoData = JSON.parse(sessionStorage.getItem(carritoKey)) || { items: [], restauranteId: null };
    let carrito = carritoData.items;
    let datosRestaurante = { lat: null, lon: null, yapeNumero: '', yapeQR: '' };
    
    const defaultLat = -10.683; 
    const defaultLng = -76.256;
    let userLat = defaultLat; 
    let userLng = defaultLng;
    
    let costoEnvioActual = 0;
    let estaEnZona = true;
    let esCargaPesada = false;

    // =========================================================
    // NUEVO: TRADUCIR COORDENADAS A DIRECCIÓN REAL
    // =========================================================
    async function autocompletarDireccion(lat, lng) {
        try {
            document.getElementById('direccion').value = "Buscando dirección...";
            // Usamos la API gratuita de Nominatim
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`);
            const data = await response.json();
            
            if (data && data.display_name) {
                const partes = data.display_name.split(',');
                // Rellenamos el text-area con la calle
                document.getElementById('direccion').value = partes[0] + (partes[1] ? ',' + partes[1] : '');
                // Rellenamos la referencia sugerida (Distrito/Ciudad)
                document.getElementById('referencia').value = partes[2] ? partes[2].trim() : '';
            } else {
                document.getElementById('direccion').value = "";
            }
        } catch (error) {
            console.error("Error obteniendo dirección:", error);
            document.getElementById('direccion').value = "";
        }
    }
    // =========================================================
    // 2. INICIALIZAR MAPA
    // =========================================================
    let mapa, marcador, poligonoZona;
    if(document.getElementById('mapa-checkout')) {
        mapa = L.map('mapa-checkout').setView([defaultLat, defaultLng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(mapa);
        
        if (zonaReparto.length > 2) {
            poligonoZona = L.polygon(zonaReparto, { color: '#198754', fillColor: '#198754', fillOpacity: 0.1, weight: 2 }).addTo(mapa);
        }
        marcador = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(mapa);
    }

    // =========================================================
    // 3. LÓGICA DE VOLUMEN (MOCHILA)
    // =========================================================
    function calcularPuntosVolumen(nombre) {
        const n = nombre.toLowerCase();
        if (n.includes('entero') || n.includes('familiar') || n.includes('banquete') || n.includes('grande')) return 4;
        if (n.includes('medio') || n.includes('1/2') || n.includes('parrilla')) return 2;
        if (n.includes('gaseosa') || n.includes('bebida') || n.includes('agua')) return 0.5;
        return 1; // Plato normal
    }

    // =========================================================
    // 4. LÓGICA DE ZONA (GEOFENCING)
    // =========================================================
    function puntoEnPoligono(lat, lng, poly) {
        var x = lat, y = lng, inside = false;
        for (var i = 0, j = poly.length - 1; i < poly.length; j = i++) {
            var xi = poly[i][0], yi = poly[i][1];
            var xj = poly[j][0], yj = poly[j][1];
            var intersect = ((yi > y) != (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
            if (intersect) inside = !inside;
        }
        return inside;
    }

    function verificarZona() {
        if (!zonaReparto || zonaReparto.length < 3) return true;
        const adentro = puntoEnPoligono(userLat, userLng, zonaReparto);
        
        if (!adentro) {
            estaEnZona = false;
            if(alertaZona) alertaZona.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill"></i> Fuera de zona de cobertura.</span>';
            if(document.getElementById('valor-envio')) document.getElementById('valor-envio').innerHTML = '<span class="badge bg-danger">Sin Cobertura</span>';
            return false;
        } else {
            estaEnZona = true;
            if(alertaZona) alertaZona.innerHTML = '<span class="text-success"><i class="bi bi-geo-alt-fill"></i> Cobertura confirmada.</span>';
            validarBotonConfirmacion();
            return true;
        }
    }

    // =========================================================
    // 5. CÁLCULO DE COSTO DE ENVÍO
    // =========================================================
    async function calcularCostoEnvio(clienteLat, clienteLon) {
        if (!datosRestaurante.lat || !datosRestaurante.lon) return 5.00;

        // 1. Calcular Volumen
        let totalPuntos = 0;
        carrito.forEach(item => { totalPuntos += calcularPuntosVolumen(item.nombre) * item.cantidad; });
        esCargaPesada = (totalPuntos > CAPACIDAD_MOCHILA);

        // 2. CÁLCULO INSTANTÁNEO (Factor de Curvatura para Pasco)
        let distanciaLineal = calcularDistanciaLineal(clienteLat, clienteLon);
        // Multiplicamos por 1.4 para simular la distancia real por carreteras/subidas
        let distanciaKm = distanciaLineal * 1.4; 

        // 3. Tarifa Base
        let costo = 5.00; 
        if (distanciaKm > 1.5) { 
            costo += (distanciaKm - 1.5) * 1.50; 
        }

        // 4. Recargos
        if (ES_MODO_LLUVIA) costo += RECARGO_LLUVIA;
        if (esCargaPesada) costo += RECARGO_CARGA_PESADA;

        return Math.round(costo * 10) / 10;
    }

    function calcularDistanciaLineal(lat1, lon1) {
        const R = 6371; 
        const dLat = (lat1 - datosRestaurante.lat) * Math.PI / 180;
        const dLon = (lon1 - datosRestaurante.lon) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(datosRestaurante.lat * Math.PI / 180) * Math.cos(lat1 * Math.PI / 180) * Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    async function actualizarTotalesEnvio() {
        if (!verificarZona()) { validarBotonConfirmacion(); return 0; }

        if(document.getElementById('valor-envio')) document.getElementById('valor-envio').innerHTML = '...';
        if(btnConfirmarInicial) btnConfirmarInicial.disabled = true;

        costoEnvioActual = await calcularCostoEnvio(userLat, userLng);
        
        // Inputs Hidden
        if(document.getElementById('input_costo_envio')) document.getElementById('input_costo_envio').value = costoEnvioActual.toFixed(2);
        if(document.getElementById('latitud')) document.getElementById('latitud').value = userLat;
        if(document.getElementById('longitud')) document.getElementById('longitud').value = userLng;

        let subtotalProductos = 0;
        carrito.forEach(i => subtotalProductos += i.precio * i.cantidad);
        const totalFinal = subtotalProductos + costoEnvioActual;

        if(totalInput) totalInput.value = totalFinal.toFixed(2);

        // UI Tabla de Costos
        const tfoot = document.querySelector('.summary-table tfoot');
        if(tfoot) {
            const filasViejas = tfoot.querySelectorAll('.fila-envio-dinamica');
            filasViejas.forEach(f => f.remove());
            
            const rowTotal = document.getElementById('row-total-final');
            
            let etiquetas = '';
            if(ES_MODO_LLUVIA) etiquetas += ' <span class="badge bg-warning text-dark"><i class="bi bi-cloud-rain"></i> Lluvia</span>';
            if(esCargaPesada) etiquetas += ' <span class="badge bg-dark"><i class="bi bi-box-seam"></i> Carga Pesada</span>';
            
            const rowEnvio = document.createElement('tr');
            rowEnvio.className = 'fila-envio-dinamica';
            rowEnvio.innerHTML = `<td colspan="2" class="text-end text-muted small pe-4">Envío ${etiquetas}</td><td class="text-end fw-bold text-primary pe-4">S/ ${costoEnvioActual.toFixed(2)}</td><td></td>`;
            
            tfoot.insertBefore(rowEnvio, rowTotal);
            
            if (document.getElementById('celda-total-final')) document.getElementById('celda-total-final').textContent = `S/ ${totalFinal.toFixed(2)}`;
        }

        validarBotonConfirmacion();
        return costoEnvioActual;
    }

    // =========================================================
    // 6. RENDERIZADO CARRITO
    // =========================================================
    if (carritoData && carritoData.restauranteId) {
        fetch(`procesos/obtener_datos_restaurante.php?id_restaurante=${carritoData.restauranteId}`)
            .then(res => res.json())
            .then(resp => {
                if (resp.status === 'success') {
                    datosRestaurante = {
                        lat: parseFloat(resp.data.latitud), lon: parseFloat(resp.data.longitud),
                        yapeNumero: resp.data.yape_numero, yapeQR: resp.data.yape_qr
                    };
                    actualizarTotalesEnvio();
                }
            });
    }

    function renderCarrito() {
        resumenDiv.innerHTML = '';
        if (carrito.length === 0) {
            resumenDiv.innerHTML = '<div class="p-4 text-center text-muted">Vacío</div>';
            checkoutForm.style.display = 'none';
            return;
        }
        checkoutForm.style.display = 'block';
        
        let htmlTabla = `<table class="table table-borderless align-middle summary-table"><thead class="table-light"><tr><th class="ps-3">Prod</th><th class="text-center">Cant</th><th class="text-end pe-3">Sub</th><th></th></tr></thead><tbody>`;
        carrito.forEach(item => {
            htmlTabla += `<tr><td class="ps-3 small">${item.nombre}</td><td class="text-center"><div class="btn-group btn-group-sm"><button class="btn btn-outline-secondary py-0" onclick="mod('${item.id}', -1)">-</button><button class="btn btn-outline-secondary py-0" disabled>${item.cantidad}</button><button class="btn btn-outline-secondary py-0" onclick="mod('${item.id}', 1)">+</button></div></td><td class="text-end pe-3 small">S/ ${(item.precio*item.cantidad).toFixed(2)}</td><td class="text-center"><i class="bi bi-x text-danger" style="cursor:pointer" onclick="del('${item.id}')"></i></td></tr>`;
        });
        htmlTabla += `</tbody><tfoot><tr id="row-total-final" class="border-top"><td colspan="2" class="text-end fw-bold ps-3">Total Pagar</td><td class="text-end fw-bold h5 pe-3" id="celda-total-final">...</td><td></td></tr></tfoot></table>`;
        
        const wrapper = document.createElement('div');
        wrapper.className = 'table-responsive';
        wrapper.innerHTML = htmlTabla;
        resumenDiv.appendChild(wrapper);
        
        carritoDataInput.value = JSON.stringify(carrito);
        restauranteIdInput.value = carritoData.restauranteId;
        actualizarTotalesEnvio();
    }

    // Funciones globales
    window.mod = (id, d) => { const i = carrito.find(x => x.id === id); if(i){ i.cantidad += d; if(i.cantidad <= 0) carrito = carrito.filter(x => x.id !== id); guardar(); }};
    window.del = (id) => { carrito = carrito.filter(x => x.id !== id); guardar(); };
    function guardar() { carritoData.items = carrito; sessionStorage.setItem(carritoKey, JSON.stringify(carritoData)); renderCarrito(); }

    // Eventos UI
    // ==========================================
    // Eventos UI
    // ==========================================
    
    // Variable global para evitar que se sature el servidor (Debounce)
    let temporizadorRuta;

    if(typeof marcador !== 'undefined'){
        // NUEVO: Globito indicando al usuario que el pin se puede mover
        marcador.bindPopup("<b>¡Arrástrame hasta tu puerta!</b><br><small>Actualizaré tu dirección sola.</small>").openPopup();

        marcador.on('dragend', function(e) {
            const pos = e.target.getLatLng(); 
            userLat = pos.lat; 
            userLng = pos.lng; 
            
            // Llama a la función de autocompletado
            autocompletarDireccion(userLat, userLng);
            
            // 1. Mostrar visualmente que está calculando (Feedback visual)
            const celdaTotal = document.getElementById('celda-total-final');
            if (celdaTotal) {
                celdaTotal.innerHTML = '<span class="spinner-border spinner-border-sm text-primary" role="status"></span> <span class="small text-muted">Calculando...</span>';
            }
            
            if(btnConfirmarInicial) {
                btnConfirmarInicial.disabled = true;
                btnConfirmarInicial.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Calculando ruta exacta...';
            }

            // 2. Limpiar el contador si el usuario sigue moviendo el pin rápido
            clearTimeout(temporizadorRuta);

            // 3. Esperar 800 milisegundos una vez que suelta el pin para calcular
            temporizadorRuta = setTimeout(() => {
                actualizarTotalesEnvio();
            }, 300);
        });
    }
    const btnGps = document.getElementById('usar-gps-btn');
    if(btnGps) {
        btnGps.addEventListener('click', function() {
            if (navigator.geolocation) {
                document.getElementById('gps-status').innerHTML = '<span class="spinner-border spinner-border-sm text-primary"></span> Buscando...';
                navigator.geolocation.getCurrentPosition(pos => {
                    userLat = pos.coords.latitude; userLng = pos.coords.longitude;
                    
                    if(mapa) { 
                        mapa.setView([userLat, userLng], 18); // Zoom más cercano al usar GPS
                        marcador.setLatLng([userLat, userLng]); 
                        marcador.openPopup();
                    }
                    
                    // Llama a la función de autocompletado en lugar de poner las coordenadas secas
                    autocompletarDireccion(userLat, userLng);
                    
                    actualizarTotalesEnvio();
                    document.getElementById('gps-status').innerHTML = '<span class="text-success fw-bold">GPS Encontrado</span>';
                }, (error) => {
                    document.getElementById('gps-status').innerHTML = '<span class="text-danger small">GPS desactivado o denegado</span>';
                });
            }
        });
    }

    // =========================================================
    // 7. VALIDACIÓN (INCLUYENDO CHECKBOX PUERTA)
    // =========================================================
    function validarBotonConfirmacion() {
        if (!selectPago || !btnConfirmarInicial) return;
        
        let ok = true; 
        let txt = 'Confirmar Pedido'; 
        let cls = 'btn-primary';

        // 1. Zona
        if(!estaEnZona) { 
            ok = false; 
            txt = 'Fuera de Zona'; 
            cls = 'btn-secondary'; 
        }
        // 2. Puerta (NUEVO)
        else if (checkPuerta && !checkPuerta.checked) {
            ok = false;
            txt = 'Acepta entrega en puerta';
            cls = 'btn-secondary';
        }
        // 3. Yape
        else if (selectPago.value === 'yape' && (!inputYapeFile.files || inputYapeFile.files.length === 0)) {
            ok = false; 
            txt = 'Falta captura de pago'; 
            cls = 'btn-secondary';
        }

        btnConfirmarInicial.disabled = !ok;
        btnConfirmarInicial.innerHTML = txt;
        btnConfirmarInicial.className = `btn btn-lg w-100 ${cls} btn-confirm-order`;
    }

    // Listeners de validación
    if(selectPago) selectPago.addEventListener('change', function() {
        containerYape.style.display = (this.value === 'yape') ? 'block' : 'none';
        divVuelto.style.display = (this.value === 'efectivo') ? 'block' : 'none';
        if(this.value === 'yape') {
            displayYapeNum.textContent = datosRestaurante.yapeNumero || "--";
            displayYapeQR.innerHTML = datosRestaurante.yapeQR ? `<img src="assets/img/qr/${datosRestaurante.yapeQR}" class="img-fluid" style="max-width:150px">` : 'Sin QR';
        }
        validarBotonConfirmacion();
    });
    
    if(inputYapeFile) inputYapeFile.addEventListener('change', validarBotonConfirmacion);
    if(checkPuerta) checkPuerta.addEventListener('change', validarBotonConfirmacion); // ESCUCHA EL CHECKBOX
    if(btnCopiar) btnCopiar.addEventListener('click', () => { navigator.clipboard.writeText(displayYapeNum.textContent); alert("Copiado"); });

    // Enviar
    if(btnConfirmarInicial) {
        btnConfirmarInicial.addEventListener('click', function(e) {
            e.preventDefault();
            if(!document.getElementById('direccion').value.trim() || !selectPago.value) return alert("Faltan datos");
            if(selectPago.value === 'efectivo' && !inputVuelto.value) return alert("Indica con cuánto pagas");
            if(!estaEnZona) return alert("Zona prohibida");

            // Modal Resumen
            const lista = document.getElementById('boleta-items'); lista.innerHTML = '';
            let sub = 0;
            carrito.forEach(i => {
                sub += i.precio * i.cantidad;
                lista.innerHTML += `<li class="list-group-item d-flex justify-content-between px-0 py-1"><small>${i.cantidad} x ${i.nombre}</small><small>S/ ${(i.precio*i.cantidad).toFixed(2)}</small></li>`;
            });
            
            // Badges en Modal
            let badgesModal = '';
            if(ES_MODO_LLUVIA) badgesModal += ' <span class="badge bg-warning text-dark">Lluvia</span>';
            if(esCargaPesada) badgesModal += ' <span class="badge bg-dark">Pesado</span>';
            
            document.getElementById('boleta-direccion').textContent = document.getElementById('direccion').value;
            document.getElementById('boleta-subtotal').textContent = `S/ ${sub.toFixed(2)}`;
            document.getElementById('boleta-envio').textContent = `S/ ${costoEnvioActual.toFixed(2)}`;
            if(document.getElementById('boleta-badges')) document.getElementById('boleta-badges').innerHTML = badgesModal;
            document.getElementById('boleta-total').textContent = `S/ ${(sub+costoEnvioActual).toFixed(2)}`;
            document.getElementById('boleta-metodo').textContent = selectPago.value;
            
            modalBoleta.show();
        });
    }
    if(btnEnviarFinal) {
        btnEnviarFinal.addEventListener('click', function() {
            this.disabled = true; this.innerHTML = 'Enviando...';
            checkoutForm.submit();
        });
    }

    renderCarrito();
    
    // Popup Lluvia
    if (ES_MODO_LLUVIA) {
        const modalHtml = `<div class="modal fade" id="modalLluviaAlerta" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-warning"><div class="modal-header bg-warning text-dark border-0"><h5 class="modal-title fw-bold">🌧️ ¡Lluvia Fuerte!</h5></div><div class="modal-body text-center"><p>Tarifa dinámica activa por seguridad.</p><h2 class="badge bg-danger fs-3">+ S/ ${RECARGO_LLUVIA.toFixed(2)}</h2></div><div class="modal-footer justify-content-center border-0"><button type="button" class="btn btn-dark btn-sm px-4" data-bs-dismiss="modal">Entendido</button></div></div></div></div>`;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        new bootstrap.Modal(document.getElementById('modalLluviaAlerta')).show();
    }
    
    if (typeof mapa !== 'undefined') setTimeout(() => mapa.invalidateSize(), 500);
});
function copiarNumero() {
    const inputNumero = document.getElementById('numero-yape');
    
    // Seleccionar el contenido del input
    inputNumero.select();
    inputNumero.setSelectionRange(0, 99999); // Para móviles

    // Copiar al portapapeles
    navigator.clipboard.writeText(inputNumero.value).then(() => {
        // Opcional: Cambiar texto del botón momentáneamente
        const btn = event.currentTarget;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> ¡Copiado!';
        btn.classList.replace('btn-primary', 'btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.replace('btn-success', 'btn-primary');
        }, 2000);
    });
}
</script>
<?php include 'includes/footer.php'; ?>