<?php
session_start();
if (!isset($_SESSION['cliente_id']) || !isset($_GET['id_pedido'])) {
    header('Location: login_cliente.php');
    exit();
}
require_once 'includes/conexion.php';

$id_pedido = $_GET['id_pedido'];
$id_cliente_sesion = $_SESSION['cliente_id'];

// NUEVO: Consulta actualizada para traer también los teléfonos
$sql = "SELECT 
            p.latitud as latitud_cliente, 
            p.longitud as longitud_cliente, 
            p.id_repartidor,
            p.direccion_pedido,
            p.estado_pedido,
            r.nombre_restaurante,
            r.direccion as direccion_restaurante,
            r.telefono as telefono_restaurante,
            rep.nombre as nombre_repartidor,
            rep.telefono as telefono_repartidor
        FROM pedidos p
        JOIN restaurantes r ON p.id_restaurante = r.id
        LEFT JOIN repartidores rep ON p.id_repartidor = rep.id
        WHERE p.id = ? AND p.id_cliente = ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_pedido, $id_cliente_sesion);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    die("Pedido no encontrado o no te pertenece.");
}

$pedido = $resultado->fetch_assoc();
$id_repartidor = $pedido['id_repartidor'];

// Variables para el diseño
$nombre_repartidor = $pedido['nombre_repartidor'] ?? 'Buscando repartidor...';
$estado_pedido = $pedido['estado_pedido'];
$direccion_restaurante = $pedido['direccion_restaurante'];
$direccion_cliente = $pedido['direccion_pedido'];

// NUEVO: Variables para los teléfonos
$telefono_restaurante = $pedido['telefono_restaurante'];
$telefono_repartidor = $pedido['telefono_repartidor'];

include 'includes/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .pin-repartidor {
        background-color: #198754; 
        color: white;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid white;
        box-shadow: 0 0 15px rgba(25, 135, 84, 0.6);
        animation: pulse-pin 1.5s infinite;
    }
    
    .pin-casa {
        background-color: #dc3545; 
        color: white;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid white;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }
    
    @keyframes pulse-pin {
        0% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7); }
        70% { box-shadow: 0 0 0 15px rgba(25, 135, 84, 0); }
        100% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
    }
</style>

<div class="hero-quickbite">
    <div class="container hero-text">
        <div class="dashboard-header d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 fw-bold">Rastreando Pedido #<?php echo $id_pedido; ?></h1>
                <?php if (is_null($id_repartidor)): ?>
                    <p class="lead text-warning mb-0"><i class="bi bi-hourglass-split me-2"></i>Esperando que un motorizado acepte tu pedido...</p>
                <?php else: ?>
                    <p class="lead text-white-50 mb-0"><?php echo htmlspecialchars($nombre_repartidor); ?> está asignado a tu entrega.</p>
                <?php endif; ?>
            </div>
            <a href="mis_pedidos.php" class="btn btn-outline-light mt-3 mt-md-0"><i class="bi bi-arrow-left me-2"></i>Volver a Mis Pedidos</a>
        </div>
    </div>
</div>

<div class="main-content-overlay">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card tracking-info-card h-100">
                    <div class="card-body">
                        
                        <div class="d-flex align-items-center mb-4">
                            <?php if (is_null($id_repartidor)): ?>
                                <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                    <i class="bi bi-search fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">Buscando motorizado</h5>
                                    <p class="text-muted mb-0">Por favor, espera un momento.</p>
                                </div>
                            <?php else: ?>
                                <img src="https://via.placeholder.com/60/198754/ffffff?text=<?php echo substr(htmlspecialchars($nombre_repartidor), 0, 1); ?>" alt="repartidor" class="rounded-circle me-3">
                                <div>
                                    <h5 class="mb-0"><?php echo htmlspecialchars($nombre_repartidor); ?></h5>
                                    <p class="text-success fw-bold mb-0">Repartidor Asignado</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <ul class="tracking-steps">
                            <li class="step-item <?php echo ($estado_pedido == 'Pendiente') ? 'active' : 'completed'; ?>">
                                <div class="step-icon">🕒</div>
                                <div class="step-label">Pendiente</div>
                            </li>
                            <li class="step-item <?php echo ($estado_pedido == 'En preparación') ? 'active' : (in_array($estado_pedido, ['Listo para recoger', 'En camino', 'Entregado']) ? 'completed' : ''); ?>">
                                <div class="step-icon">🍳</div>
                                <div class="step-label">En preparación</div>
                            </li>
                            <li class="step-item <?php echo ($estado_pedido == 'Listo para recoger') ? 'active' : (in_array($estado_pedido, ['En camino', 'Entregado']) ? 'completed' : ''); ?>">
                                <div class="step-icon">🛍️</div>
                                <div class="step-label">Listo para recoger</div>
                            </li>
                            <li class="step-item <?php echo ($estado_pedido == 'En camino') ? 'active' : ($estado_pedido == 'Entregado' ? 'completed' : ''); ?>">
                                <div class="step-icon">🛵</div>
                                <div class="step-label">En camino</div>
                            </li>
                        </ul>
                        
                        <hr class="my-4">

                        <div class="d-grid mb-4">
                            <?php if (is_null($id_repartidor)): ?>
                                <?php 
                                    // Limpiamos el número (solo dejamos números) y aseguramos el prefijo 51 de Perú
                                    $num_rest = preg_replace('/[^0-9]/', '', $telefono_restaurante);
                                    if (strlen($num_rest) == 9) { $num_rest = '51' . $num_rest; }
                                ?>
                                <a href="https://wa.me/<?php echo $num_rest; ?>?text=Hola,%20tengo%20una%20consulta%20sobre%20mi%20pedido%20%23<?php echo $id_pedido; ?>" target="_blank" class="btn btn-outline-success btn-lg rounded-pill fw-bold shadow-sm">
                                    <i class="bi bi-whatsapp me-2"></i> WhatsApp al Restaurante
                                </a>
                            <?php else: ?>
                                <?php 
                                    $num_rep = preg_replace('/[^0-9]/', '', $telefono_repartidor);
                                    if (strlen($num_rep) == 9) { $num_rep = '51' . $num_rep; }
                                ?>
                                <a href="https://wa.me/<?php echo $num_rep; ?>?text=Hola,%20soy%20el%20cliente%20del%20pedido%20%23<?php echo $id_pedido; ?>" target="_blank" class="btn btn-success btn-lg rounded-pill fw-bold shadow">
                                    <i class="bi bi-whatsapp me-2"></i> WhatsApp a <?php echo explode(' ', trim($nombre_repartidor))[0]; ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="route-point pickup mb-3">
                            <strong>Recoger en: <?php echo htmlspecialchars($pedido['nombre_restaurante']); ?></strong>
                            <small><?php echo htmlspecialchars($direccion_restaurante); ?></small>
                        </div>
                        <div class="route-point dropoff">
                            <strong>Entregar en: Tu ubicación</strong>
                            <small><?php echo htmlspecialchars($direccion_cliente); ?></small>
                        </div>

                    </div>
                </div>
            </div>
            
            <div class="col-lg-7">
                <div id="mapa" class="shadow-sm" style="height: 600px; border-radius: 0.75rem;"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const latCliente = <?php echo $pedido['latitud_cliente'] ?? 'null'; ?>;
    const lonCliente = <?php echo $pedido['longitud_cliente'] ?? 'null'; ?>;
    const idRepartidor = <?php echo $id_repartidor ? $id_repartidor : 'null'; ?>;

    const centroMapa = (latCliente && lonCliente) ? [latCliente, lonCliente] : [-10.667, -76.256];
    const mapa = L.map('mapa').setView(centroMapa, 16);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(mapa);

    // Pin de la casa
    const iconoCasa = L.divIcon({
        className: 'custom-div-icon',
        html: '<div class="pin-casa"><i class="bi bi-house-door-fill fs-4"></i></div>',
        iconSize: [45, 45],
        iconAnchor: [22, 45],
        popupAnchor: [0, -40]
    });

    if (latCliente && lonCliente) {
        L.marker([latCliente, lonCliente], { icon: iconoCasa })
         .addTo(mapa)
         .bindPopup('<b class="text-danger">Tu ubicación de entrega</b>');
    }

    // Pin del repartidor y Fetch
    if (idRepartidor !== null) {
        const iconoRepartidor = L.divIcon({
            className: 'custom-div-icon',
            html: '<div class="pin-repartidor"><i class="bi bi-scooter fs-4"></i></div>',
            iconSize: [45, 45],
            iconAnchor: [22, 45],
            popupAnchor: [0, -40]
        });
        
        let marcadorRepartidor = L.marker(centroMapa, { icon: iconoRepartidor })
                                  .addTo(mapa)
                                  .bindPopup('<b class="text-success">🛵 Tu pedido está en camino</b>');

        async function actualizarUbicacion() {
            try {
                const response = await fetch(`procesos/obtener_ubicacion_repartidor.php?id_repartidor=${idRepartidor}`);
                const data = await response.json();

                if (data.status === 'success') {
                    const nuevaPosicion = [data.latitud, data.longitud];
                    marcadorRepartidor.setLatLng(nuevaPosicion);
                }
            } catch (error) {
                console.error("Error al obtener la ubicación:", error);
            }
        }
        
        setInterval(actualizarUbicacion, 5000);
        actualizarUbicacion();
    } else {
        L.popup()
            .setLatLng(centroMapa)
            .setContent("<div class='text-center p-2'><b>Buscando un repartidor cercano... 🛵</b><br><small class='text-muted'>Por favor espera</small></div>")
            .openOn(mapa);
    }
});
</script>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>