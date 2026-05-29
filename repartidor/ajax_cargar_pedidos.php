<?php
session_start();
require_once '../includes/conexion.php';

if (!isset($_SESSION['repartidor_id'])) { die(); }

// 1. Agregamos p.foto_yape a la consulta
$sql = "SELECT p.id, p.fecha_pedido, p.monto_total, p.costo_envio, p.estado_pedido, 
               p.direccion_pedido, p.referencia, p.telefono_pedido, p.metodo_pago, p.foto_yape,
               r.nombre_restaurante, r.direccion as direccion_restaurante
        FROM pedidos p
        JOIN restaurantes r ON p.id_restaurante = r.id
        WHERE p.estado_pedido = 'Pendiente' AND p.id_repartidor IS NULL
        ORDER BY p.fecha_pedido ASC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<div class="row g-4">
<?php if ($resultado->num_rows > 0):
    while($pedido = $resultado->fetch_assoc()): ?>
    
    <div class="col-md-12">
        <div class="card shadow border-success">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">¡Nuevo Pedido #<?php echo $pedido['id']; ?>!</h5>
                <span class="badge bg-light text-dark fs-6">S/ <?php echo number_format($pedido['costo_envio'], 2); ?> Ganancia</span>
            </div>
            
            <div class="card-body">
                <div class="route-info">
                    <div class="mb-3">
                        <i class="bi bi-shop text-primary"></i> <strong>Recoger en:</strong> <?php echo htmlspecialchars($pedido['nombre_restaurante']); ?>
                    </div>
                    
                    <div class="mb-3">
                        <i class="bi bi-person text-success"></i> <strong>Entregar a:</strong> <?php echo htmlspecialchars($pedido['direccion_pedido']); ?>
                        
                        <?php if (!empty($pedido['telefono_pedido'])): ?>
                            <div class="mt-2">
                                <a href="https://wa.me/51<?php echo htmlspecialchars($pedido['telefono_pedido']); ?>?text=Hola,%20te%20escribo%20de%20CerroDelivery%20por%20tu%20pedido%20%23." target="_blank" class="btn btn-sm btn-outline-success fw-bold">
                                    <i class="bi bi-whatsapp me-1"></i> Contactar Cliente
                                </a>
                            </div>
                        <?php endif; ?>
                        </div>
                    
                    <div class="mb-3 p-3 bg-light rounded border">
                        <i class="bi bi-wallet2 text-warning me-2"></i><strong>Método de pago:</strong> <span class="text-uppercase fw-bold"><?php echo htmlspecialchars($pedido['metodo_pago']); ?></span>
                        
                        <?php if ($pedido['metodo_pago'] == 'yape' && !empty($pedido['foto_yape'])): ?>
                            <div class="mt-2">
                                <a href="../assets/img/comprobantes/<?php echo htmlspecialchars($pedido['foto_yape']); ?>" target="_blank" class="btn btn-sm btn-outline-info fw-bold">
                                    <i class="bi bi-image me-1"></i> Ver Captura de Yape
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                </div>
            </div>

            <div class="card-footer bg-white p-3">
                <form action="../procesos/aceptar_pedido.php" method="POST" class="w-100">
                    <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                    <button type="submit" class="btn btn-success w-100 fw-bold py-3 fs-5">
                        <i class="bi bi-check-circle-fill me-2"></i>Aceptar Pedido
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <?php endwhile;
else: ?>
    <div class="col-12 text-center p-5 bg-light rounded-3 border">
        <h4 class="fw-bold">No hay pedidos nuevos</h4>
        <p class="text-muted">Espera un momento, los pedidos disponibles aparecerán aquí.</p>
    </div>
<?php endif; ?>
</div>

<?php
$stmt->close();
$conn->close();
?>