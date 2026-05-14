<?php
session_start();
require_once '../includes/conexion.php';

if (!isset($_SESSION['restaurante_id'])) { die(); }
$id_restaurante = $_SESSION['restaurante_id'];

$sql_pedidos = "SELECT p.id, p.fecha_pedido, p.monto_total, p.costo_envio, p.estado_pedido, 
                       c.nombre as nombre_cliente, u.nombre as nombre_repartidor
                FROM pedidos p
                JOIN usuarios_clientes c ON p.id_cliente = c.id
                LEFT JOIN usuarios_repartidores u ON p.id_repartidor = u.id
                WHERE p.id_restaurante = ?
                ORDER BY p.fecha_pedido DESC";
                
$stmt_pedidos = $conn->prepare($sql_pedidos);
$stmt_pedidos->bind_param("i", $id_restaurante);
$stmt_pedidos->execute();
$resultado_pedidos = $stmt_pedidos->get_result();

if ($resultado_pedidos->num_rows > 0):
    while ($pedido = $resultado_pedidos->fetch_assoc()):
?>
    <div class="card mb-3 shadow-sm border-secondary">
        <div class="card-body row align-items-center">
            <div class="col-md-4">
                <h5 class="fw-bold mb-1">Pedido #<?php echo $pedido['id']; ?></h5>
                <p class="mb-0 text-muted small"><?php echo date('d/m/Y h:i A', strtotime($pedido['fecha_pedido'])); ?></p>
            </div>
            
            <div class="col-md-4 text-center">
                <h6 class="text-uppercase small mb-1">Estado del Pedido</h6>
                <span class="badge bg-dark px-3 py-2 fs-6">
                    <?php echo htmlspecialchars($pedido['estado_pedido']); ?>
                </span>
            </div>

            <div class="col-md-4 text-end">
                <span class="d-block small text-muted">Tu Ganancia Neta:</span>
                <span class="fw-bold fs-4 text-success">
                    S/ <?php echo number_format($pedido['monto_total'] - $pedido['costo_envio'], 2); ?>
                </span>
            </div>
        </div>
        <div class="card-footer bg-white border-top-0 pt-0">
             <small class="text-muted italic">
                <i class="bi bi-info-circle me-1"></i>
                Gestionado por repartidor: <?php echo $pedido['nombre_repartidor'] ?? 'Buscando...'; ?>
             </small>
        </div>
    </div>
<?php 
    endwhile;
// ... (resto del código) ...
else: 
?>
    <div class="text-center p-5">
        <h4 class="fw-bold">No tienes pedidos activos</h4>
    </div>
<?php endif; ?>