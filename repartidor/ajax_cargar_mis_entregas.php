<?php
session_start();
require_once '../includes/conexion.php';
if (!isset($_SESSION['repartidor_id'])) { die(); }
$id_repartidor = $_SESSION['repartidor_id'];

// Incluimos latitud y longitud en la consulta
$sql = "SELECT p.id, p.direccion_pedido, p.latitud, p.longitud, c.nombre as nombre_cliente, c.telefono as telefono_cliente, r.nombre_restaurante, r.direccion as direccion_restaurante
        FROM pedidos p
        JOIN restaurantes r ON p.id_restaurante = r.id
        JOIN usuarios_clientes c ON p.id_cliente = c.id
        WHERE p.id_repartidor = ? AND p.estado_pedido = 'En camino'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_repartidor);
$stmt->execute();
$resultado_entregas = $stmt->get_result();
?>

<div class="row g-4">
<?php if ($resultado_entregas->num_rows > 0):
    while ($entrega = $resultado_entregas->fetch_assoc()): ?>
    <div class="col-md-6">
        <div class="card active-delivery-card h-100 shadow-sm">
             <div class="card-header bg-success text-white">
                <h5 class="mb-0 fw-bold"><i class="bi bi-truck me-2"></i>En curso: Pedido #<?php echo $entrega['id']; ?></h5>
            </div>
            <div class="card-body">
                <div class="route-info mb-4">
                    <div class="route-point pickup">
                        <i class="bi bi-shop icon"></i>
                        <div>
                            <small class="text-muted">RECOGER EN</small>
                            <strong><?php echo htmlspecialchars($entrega['nombre_restaurante']); ?></strong>
                        </div>
                    </div>
                    <div class="route-line"></div>
                    <div class="route-point dropoff">
                        <i class="bi bi-house-door-fill icon"></i>
                        <div>
                            <small class="text-muted">ENTREGAR A</small>
                            <strong><?php echo htmlspecialchars($entrega['nombre_cliente']); ?></strong>
                            <span class="d-block text-muted small"><?php echo htmlspecialchars($entrega['direccion_pedido']); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <a href="https://wa.me/51<?php echo htmlspecialchars($entrega['telefono_cliente']); ?>?text=Hola, soy tu repartidor de CerroDelivery para el pedido #<?php echo $entrega['id']; ?>." target="_blank" class="btn btn-outline-success">
                        <i class="bi bi-whatsapp me-2"></i>Contactar Cliente
                    </a>
                    
                    <?php if (!empty($entrega['latitud']) && !empty($entrega['longitud'])): ?>
                        <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo $entrega['latitud']; ?>,<?php echo $entrega['longitud']; ?>" target="_blank" class="btn btn-outline-primary">
                            <i class="bi bi-geo-alt-fill me-2"></i>IR CON GPS (Google Maps)
                        </a>
                    <?php else: ?>
                         <button class="btn btn-secondary" disabled>
                            <i class="bi bi-geo-alt-fill me-2"></i>Sin GPS (Ver Dirección)
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer bg-light p-3">
                 <form action="../procesos/completar_entrega.php" method="POST">
                    <input type="hidden" name="id_pedido" value="<?php echo $entrega['id']; ?>">
                    <button type="submit" class="btn btn-success w-100 btn-lg fw-bold">Marcar como Entregado</button>
                </form>
            </div>
        </div>
    </div>
    <?php endwhile;
else: ?>
    <div class="col-12">
        <div class="text-center p-5 bg-light rounded-3">
            <img src="../assets/img/no-orders-repartidor.png" alt="Sin entregas" style="width: 180px;" class="mb-3">
            <h4 class="fw-bold">No tienes entregas activas</h4>
            <p class="text-muted">Cuando aceptes un pedido, aparecerá aquí.</p>
        </div>
    </div>
<?php endif; ?>
</div>

<?php
$stmt->close();
$conn->close();
?>