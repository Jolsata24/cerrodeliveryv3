<?php
session_start();
if (!isset($_SESSION['cliente_id']) || !isset($_GET['id_pedido'])) {
    header('Location: login_cliente.php');
    exit();
}
require_once 'includes/conexion.php';

$id_pedido = $_GET['id_pedido'];
$id_cliente_sesion = $_SESSION['cliente_id'];

// --- CONSULTA (SIN CAMBIOS) ---
$sql = "SELECT 
            p.latitud as latitud_cliente, 
            p.longitud as longitud_cliente, 
            p.id_repartidor,
            p.direccion_pedido,
            p.estado_pedido,
            r.nombre_restaurante,
            r.direccion as direccion_restaurante,
            rep.nombre as nombre_repartidor
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

if (is_null($id_repartidor)) {
    header('Location: mis_pedidos.php?error=no_repartidor');
    exit();
}

// Variables para el nuevo diseño (SIN CAMBIOS)
$nombre_repartidor = $pedido['nombre_repartidor'] ?? 'Repartidor Asignado';
$estado_pedido = $pedido['estado_pedido'];
$direccion_restaurante = $pedido['direccion_restaurante'];
$direccion_cliente = $pedido['direccion_pedido'];

include 'includes/header.php';
?>

<!-- 
  Enlaces de Leaflet.js
  Los mantenemos aquí (en el body) como en tu archivo original.
-->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- === INICIO DE LA MODIFICACIÓN ESTRUCTURAL === -->

<!-- 1. NUEVO HERO SECTION -->
<div class="hero-quickbite">
    <div class="container hero-text">
        <div class="dashboard-header d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 fw-bold">Rastreando Pedido #<?php echo $id_pedido; ?></h1>
                <p class="lead text-white-50 mb-0"><?php echo htmlspecialchars($nombre_repartidor); ?> está en camino con tu comida.</p>
            </div>
            <!-- Botón para volver a Mis Pedidos -->
            <a href="mis_pedidos.php" class="btn btn-outline-light mt-3 mt-md-0"><i class="bi bi-arrow-left me-2"></i>Volver a Mis Pedidos</a>
        </div>
    </div>
</div>

<!-- 2. NUEVO MAIN CONTENT OVERLAY -->
<div class="main-content-overlay">
    <div class="container">
        
        <!-- 
          El contenido original (el .row) se mueve aquí dentro.
          He quitado el div.container y el botón "Seguir Comprando"
          que estaban en tu archivo original, ya que ahora están en el hero.
        -->
        <div class="row g-4">
            
            <div class="col-lg-5">
                <div class="card tracking-info-card h-100">
                    <div class="card-body">
                        <!-- Contenido de la tarjeta de estado (SIN CAMBIOS) -->
                        <div class="d-flex align-items-center mb-4">
                            <img src="https://via.placeholder.com/60/007bff/ffffff?text=<?php echo substr($nombre_repartidor, 0, 1); ?>" alt="repartidor" class="rounded-circle me-3">
                            <div>
                                <h5 class="mb-0"><?php echo htmlspecialchars($nombre_repartidor); ?></h5>
                                <p class="text-muted mb-0">Está en camino con tu pedido.</p>
                            </div>
                        </div>
                        
                        <ul class="tracking-steps">
                            <li class="step-item <?php echo ($estado_pedido == 'En preparación') ? 'active' : 'completed'; ?>">
                                <div class="step-icon">📦</div>
                                <div class="step-label">En preparación</div>
                            </li>
                            <li class="step-item <?php echo ($estado_pedido == 'Listo para recoger') ? 'active' : 'completed'; ?>">
                                <div class="step-icon">🛍️</div>
                                <div class="step-label">Listo para recoger</div>
                            </li>
                            <li class="step-item <?php echo ($estado_pedido == 'En camino') ? 'active' : 'completed'; ?>">
                                <div class="step-icon">🛵</div>
                                <div class="step-label">En camino</div>
                            </li>
                            <li class="step-item">
                                <div class="step-icon">🏠</div>
                                <div class="step-label">Entregado</div>
                            </li>
                        </ul>
                        
                        <hr class="my-4">

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
        <!-- Fin del contenido original -->
        
    </div>
</div>
<!-- === FIN DE LA MODIFICACIÓN ESTRUCTURAL === -->


<!-- 
  SCRIPT DE LEAFLET (SIN CAMBIOS)
  Funciona igual porque el ID 'mapa' se conserva.
-->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Coordenadas del cliente (si las proporcionó)
    const latCliente = <?php echo $pedido['latitud_cliente'] ?? 'null'; ?>;
    const lonCliente = <?php echo $pedido['longitud_cliente'] ?? 'null'; ?>;
    const idRepartidor = <?php echo $id_repartidor; ?>;

    // Centrar el mapa en la ubicación del cliente o en una ubicación por defecto
    const centroMapa = (latCliente && lonCliente) ? [latCliente, lonCliente] : [-12.046374, -77.042793]; // Coordenadas de Lima por defecto

    const mapa = L.map('mapa').setView(centroMapa, 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(mapa);

    // Marcador para el cliente (tu casa)
    if (latCliente && lonCliente) {
        // Icono personalizado para el cliente (Casa)
        const iconoCasa = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png', // Marcador azul
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });
        L.marker([latCliente, lonCliente], { icon: iconoCasa }).addTo(mapa).bindPopup('<b>Tu ubicación de entrega</b>');
    }

    // Marcador para el repartidor (este se moverá)
    const iconoRepartidor = L.icon({ // Icono personalizado para el repartidor
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png', // Marcador verde
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34]
    });
    
    let marcadorRepartidor = L.marker(centroMapa, { 
        icon: iconoRepartidor
    }).addTo(mapa).bindPopup('<b>Repartidor</b>');

    // Función para obtener y actualizar la ubicación del repartidor (sin cambios)
    async function actualizarUbicacion() {
        try {
            const response = await fetch(`procesos/obtener_ubicacion_repartidor.php?id_repartidor=${idRepartidor}`);
            const data = await response.json();

            if (data.status === 'success') {
                const nuevaPosicion = [data.latitud, data.longitud];
                marcadorRepartidor.setLatLng(nuevaPosicion);
                
                // Opcional: Centrar el mapa entre el repartidor y el cliente
                if (latCliente && lonCliente) {
                    mapa.fitBounds([
                        [latCliente, lonCliente],
                        nuevaPosicion
                    ], { padding: [50, 50] }); // Añade un poco de espacio
                } else {
                    mapa.setView(nuevaPosicion, 16); // Si no hay ubicación de cliente, solo sigue al repartidor
                }
            } else {
                console.warn(data.message);
            }
        } catch (error) {
            console.error("Error al obtener la ubicación:", error);
        }
    }
    
    // Llamar a la función cada 5 segundos
    setInterval(actualizarUbicacion, 5000);
    actualizarUbicacion(); // Primera llamada inmediata
});
</script>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>