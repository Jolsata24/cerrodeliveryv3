<?php
session_start();
require_once '../includes/conexion.php';

if (!isset($_SESSION['restaurante_id'])) { die(); }
$id_restaurante = $_SESSION['restaurante_id'];

// CORRECCIÓN 1: Cambiado a la tabla correcta 'repartidores'
$sql_pedidos = "SELECT p.id, p.fecha_pedido, p.monto_total, p.costo_envio, p.estado_pedido, 
                       c.nombre as nombre_cliente, u.nombre as nombre_repartidor
                FROM pedidos p
                JOIN usuarios_clientes c ON p.id_cliente = c.id
                LEFT JOIN repartidores u ON p.id_repartidor = u.id
                WHERE p.id_restaurante = ? AND p.estado_pedido != 'Entregado'
                ORDER BY p.fecha_pedido DESC";
                
$stmt_pedidos = $conn->prepare($sql_pedidos);
$stmt_pedidos->bind_param("i", $id_restaurante);
$stmt_pedidos->execute();
$resultado_pedidos = $stmt_pedidos->get_result();

if ($resultado_pedidos->num_rows > 0):
    while ($pedido = $resultado_pedidos->fetch_assoc()):
        
        // CORRECCIÓN 2: Obtener los platos exactos que pidió el cliente para este pedido
        $id_pedido_actual = $pedido['id'];
        $sql_detalles = "SELECT cantidad, nombre_plato FROM detalle_pedidos WHERE id_pedido = ?";
        $stmt_det = $conn->prepare($sql_detalles);
        $stmt_det->bind_param("i", $id_pedido_actual);
        $stmt_det->execute();
        $resultado_detalles = $stmt_det->get_result();
?>
    <div class="card mb-4 shadow border-0" style="border-left: 5px solid <?php echo ($pedido['estado_pedido'] == 'Pendiente') ? '#dc3545' : '#0d6efd'; ?> !important;">
        <div class="card-body row align-items-center">
            
            <div class="col-md-3">
                <h5 class="fw-bold mb-1">Pedido #<?php echo $pedido['id']; ?></h5>
                <p class="mb-0 text-muted small"><i class="bi bi-clock me-1"></i><?php echo date('d/m/Y h:i A', strtotime($pedido['fecha_pedido'])); ?></p>
                <span class="badge <?php echo ($pedido['estado_pedido'] == 'Pendiente') ? 'bg-danger' : 'bg-primary'; ?> mt-2 px-3 py-2">
                    <?php echo htmlspecialchars($pedido['estado_pedido']); ?>
                </span>
            </div>
            
            <div class="col-md-6 border-start border-end py-2">
                <h6 class="text-uppercase small mb-2 fw-bold" style="color: #6c757d;"><i class="bi bi-list-check me-2"></i>A COCINAR:</h6>
                <ul class="list-unstyled mb-0 fs-5">
                    <?php while($detalle = $resultado_detalles->fetch_assoc()): ?>
                        <li class="mb-1"><span class="badge bg-dark me-2"><?php echo $detalle['cantidad']; ?>x</span> <?php echo htmlspecialchars($detalle['nombre_plato']); ?></li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <div class="col-md-3 text-end">
                <span class="d-block small text-muted text-uppercase fw-bold">Ingreso Neto:</span>
                <span class="fw-bold fs-3 text-success">
                    S/ <?php echo number_format($pedido['monto_total'] - $pedido['costo_envio'], 2); ?>
                </span>
            </div>
        </div>
        
        <div class="card-footer <?php echo is_null($pedido['nombre_repartidor']) ? 'bg-warning text-dark' : 'bg-success text-white'; ?> border-top-0 py-2">
             <small class="fw-bold fs-6">
                <?php if(is_null($pedido['nombre_repartidor'])): ?>
                    <i class="bi bi-hourglass-split me-2"></i> ¡NUEVO PEDIDO! Esperando que un motorizado lo acepte...
                <?php else: ?>
                    <i class="bi bi-motorcycle me-2"></i> Motorizado en camino: <?php echo htmlspecialchars($pedido['nombre_repartidor']); ?>
                <?php endif; ?>
             </small>
        </div>
    </div>
<?php 
    $stmt_det->close();
    endwhile;
else: 
?>
    <div class="text-center p-5 bg-light rounded-3 border">
        <h4 class="fw-bold">Sin pedidos activos</h4>
        <p class="text-muted">Cuando un cliente realice una compra, aparecerá aquí automáticamente para que comiences a cocinar.</p>
    </div>
<?php 
endif; 
$stmt_pedidos->close();
$conn->close();
?>