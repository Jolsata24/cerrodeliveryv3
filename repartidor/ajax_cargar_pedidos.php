<?php
session_start();
require_once '../includes/conexion.php';

if (!isset($_SESSION['repartidor_id'])) { die(); }
$id_repartidor = $_SESSION['repartidor_id'];

// Consultamos los pedidos que este motorizado ya aceptó y aún no entrega
$sql = "SELECT p.id, p.fecha_pedido, p.monto_total, p.costo_envio, p.estado_pedido, 
               p.direccion_pedido, p.referencia, p.telefono_pedido, p.metodo_pago,
               r.nombre_restaurante, r.direccion as direccion_restaurante
        FROM pedidos p
        JOIN restaurantes r ON p.id_restaurante = r.id
        WHERE p.id_repartidor = ? AND p.estado_pedido IN ('En preparación', 'Listo para recoger', 'En camino')
        ORDER BY p.fecha_pedido ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_repartidor);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<div class="row g-4">
<?php if ($resultado->num_rows > 0):
    while($pedido = $resultado->fetch_assoc()): ?>
    
    <div class="col-md-12">
        <div class="card delivery-job-card shadow border-dark">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Pedido #<?php echo $pedido['id']; ?></h5>
                <span class="badge bg-light text-dark fs-6"><?php echo htmlspecialchars($pedido['estado_pedido']); ?></span>
            </div>
            
            <div class="card-body">
                <div class="row">
                    <div class="col-md-7 border-end">
                        <div class="route-info">
                            <div class="route-point pickup mb-3">
                                <i class="bi bi-shop text-primary"></i>
                                <div>
                                    <small class="text-muted">RECOGER EN:</small><br>
                                    <strong><?php echo htmlspecialchars($pedido['nombre_restaurante']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($pedido['direccion_restaurante']); ?></small>
                                </div>
                            </div>
                            <div class="route-point dropoff">
                                <i class="bi bi-person text-success"></i>
                                <div>
                                    <small class="text-muted">ENTREGAR A:</small><br>
                                    <strong><?php echo htmlspecialchars($pedido['direccion_pedido']); ?></strong><br>
                                    <small class="text-muted">Ref: <?php echo htmlspecialchars($pedido['referencia']); ?></small><br>
                                    <strong>Tel: <?php echo htmlspecialchars($pedido['telefono_pedido']); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-5 text-center d-flex flex-column justify-content-center">
                        <h6 class="text-muted small text-uppercase">Dinero a Gestionar</h6>
                        <div class="p-2 bg-light rounded mb-2 border">
                            <span class="d-block small fw-bold">Total del Pedido:</span>
                            <span class="fs-5 text-dark fw-bold">S/ <?php echo number_format($pedido['monto_total'], 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small text-danger">Pagar a restaurante:</span>
                            <span class="fw-bold text-danger">S/ <?php echo number_format($pedido['monto_total'] - $pedido['costo_envio'], 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="small text-success">Tu ganancia:</span>
                            <span class="fw-bold text-success">S/ <?php echo number_format($pedido['costo_envio'], 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white p-3">
                <form action="../procesos/actualizar_estado_pedido.php" method="POST" class="w-100">
                    <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                    
                    <?php if ($pedido['estado_pedido'] == 'En preparación'): ?>
                        <input type="hidden" name="nuevo_estado" value="Listo para recoger">
                        <button type="submit" class="btn btn-warning w-100 fw-bold py-2 fs-5 text-dark">
                            <i class="bi bi-geo-alt-fill me-2"></i>Llegué al Restaurante (Esperando comida)
                        </button>
                    
                    <?php elseif ($pedido['estado_pedido'] == 'Listo para recoger'): ?>
                        <input type="hidden" name="nuevo_estado" value="En camino">
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2 fs-5">
                            <i class="bi bi-scooter me-2"></i>Ya tengo la comida ¡Ir al Cliente!
                        </button>
                    
                    <?php elseif ($pedido['estado_pedido'] == 'En camino'): ?>
                        <input type="hidden" name="nuevo_estado" value="Entregado">
                        <button type="submit" class="btn btn-success w-100 fw-bold py-3 fs-4" onclick="return confirm('¿Confirmas que ya entregaste la comida al cliente?');">
                            <i class="bi bi-check2-circle me-2"></i>Marcar como ENTREGADO
                        </button>
                    <?php endif; ?>

                </form>
            </div>
        </div>
    </div>
    
    <?php endwhile;
else: ?>
    <div class="col-12 text-center p-5 bg-light rounded-3 border">
        <h4 class="fw-bold">No tienes entregas en curso</h4>
        <p class="text-muted">Ve a la pestaña de "Nuevos Pedidos" para tomar un servicio.</p>
    </div>
<?php endif; ?>
</div>

<?php
$stmt->close();
$conn->close();
?>