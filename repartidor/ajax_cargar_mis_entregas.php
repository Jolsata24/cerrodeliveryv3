<?php
session_start();
require_once '../includes/conexion.php';

if (!isset($_SESSION['repartidor_id'])) { die(); }
$id_repartidor = $_SESSION['repartidor_id'];

// CORRECCIÓN APLICADA: Ahora hacemos el JOIN con "usuarios_clientes" en lugar de "clientes"
$sql = "SELECT p.id, p.fecha_pedido, p.monto_total, p.costo_envio, p.estado_pedido, 
               p.direccion_pedido, p.referencia, p.telefono_pedido, p.metodo_pago,
               p.latitud, p.longitud,
               r.nombre_restaurante, r.direccion as direccion_restaurante,
               r.telefono as telefono_restaurante,
               c.nombre as nombre_cliente
        FROM pedidos p
        JOIN restaurantes r ON p.id_restaurante = r.id
        LEFT JOIN usuarios_clientes c ON p.id_cliente = c.id
        WHERE p.id_repartidor = ? AND p.estado_pedido IN ('En preparación', 'Listo para recoger', 'En camino')
        ORDER BY p.fecha_pedido ASC";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_repartidor);
$stmt->execute();
$resultado_entregas = $stmt->get_result();
?>

<div class="row g-4">
<?php if ($resultado_entregas->num_rows > 0):
    while ($entrega = $resultado_entregas->fetch_assoc()): ?>
    <div class="col-md-6">
        <div class="card active-delivery-card h-100 shadow border-dark">
             <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-truck me-2"></i>Pedido #<?php echo $entrega['id']; ?></h5>
                <span class="badge bg-light text-dark fs-6"><?php echo htmlspecialchars($entrega['estado_pedido']); ?></span>
            </div>
            <div class="card-body">
                <div class="route-info mb-4">
                    <div class="route-point pickup mb-3">
                        <i class="bi bi-shop text-primary"></i>
                        <div>
                            <small class="text-muted">RECOGER EN:</small><br>
                            <strong><?php echo htmlspecialchars($entrega['nombre_restaurante']); ?></strong><br>
                            <small><?php echo htmlspecialchars($entrega['direccion_restaurante']); ?></small><br>
                            
                            <?php if (!empty($entrega['telefono_restaurante'])): ?>
                                <a href="tel:<?php echo $entrega['telefono_restaurante']; ?>" class="btn btn-sm btn-success mt-2 fw-bold">
                                    <i class="bi bi-telephone-fill me-1"></i> Llamar Restaurante
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="route-point dropoff">
                        <i class="bi bi-house-door-fill text-success fs-4"></i>
                        <div class="ms-3">
                            <small class="text-muted">ENTREGAR A</small><br>
                            <strong><?php echo htmlspecialchars($entrega['nombre_cliente'] ?? 'Cliente'); ?></strong><br>
                            <span class="d-block text-muted small"><?php echo htmlspecialchars($entrega['direccion_pedido']); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <a href="https://wa.me/51<?php echo htmlspecialchars($entrega['telefono_pedido']); ?>?text=Hola, soy tu repartidor de CerroDelivery. Te escribo por tu pedido #<?php echo $entrega['id']; ?>." target="_blank" class="btn btn-outline-success fw-bold">
                        <i class="bi bi-whatsapp me-2"></i>Contactar Cliente
                    </a>
                    
                    <?php if (!empty($entrega['latitud']) && !empty($entrega['longitud'])): ?>
                        <a href="https://www.google.com/maps/search/?api=1&query=<?php echo $entrega['latitud']; ?>,<?php echo $entrega['longitud']; ?>" target="_blank" class="btn btn-outline-primary fw-bold">
                            <i class="bi bi-geo-alt-fill me-2"></i>Ir con GPS (Google Maps)
                        </a>
                    <?php else: ?>
                         <button class="btn btn-secondary fw-bold" disabled>
                            <i class="bi bi-geo-alt-fill me-2"></i>Sin GPS (Ver Dirección)
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-footer bg-white p-3">
                 <form action="../procesos/actualizar_estado_pedido.php" method="POST" class="w-100">
                    <input type="hidden" name="id_pedido" value="<?php echo $entrega['id']; ?>">
                    
                    <?php if ($entrega['estado_pedido'] == 'En preparación'): ?>
                        <input type="hidden" name="nuevo_estado" value="Listo para recoger">
                        <button type="submit" class="btn btn-warning w-100 fw-bold py-2 fs-5 text-dark">
                            <i class="bi bi-geo-alt-fill me-2"></i>Llegué al Restaurante
                        </button>
                    
                    <?php elseif ($entrega['estado_pedido'] == 'Listo para recoger'): ?>
                        <input type="hidden" name="nuevo_estado" value="En camino">
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2 fs-5">
                            <i class="bi bi-scooter me-2"></i>Tengo la comida ¡Voy al Cliente!
                        </button>
                    
                    <?php elseif ($entrega['estado_pedido'] == 'En camino'): ?>
                        <input type="hidden" name="nuevo_estado" value="Entregado">
                        <button type="submit" class="btn btn-success w-100 fw-bold py-3 fs-4" onclick="return confirm('¿Confirmas que entregaste la comida?');">
                            <i class="bi bi-check2-circle me-2"></i>¡PEDIDO ENTREGADO!
                        </button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    <?php endwhile;
else: ?>
    <div class="col-12">
        <div class="text-center p-5 bg-light rounded-3 border">
            <img src="../assets/img/no-orders-repartidor.png" alt="Sin entregas" style="width: 150px;" class="mb-3 opacity-50">
            <h4 class="fw-bold">No tienes entregas activas</h4>
            <p class="text-muted">Ve al Panel Principal para aceptar nuevos pedidos.</p>
        </div>
    </div>
<?php endif; ?>
</div>

<?php
$stmt->close();
$conn->close();
?>