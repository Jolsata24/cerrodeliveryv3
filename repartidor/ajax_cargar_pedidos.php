<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad: solo repartidores logueados
if (!isset($_SESSION['repartidor_id'])) {
    die(); 
}
$id_repartidor = $_SESSION['repartidor_id'];

// --- CONSULTA SEGURA Y FILTRADA ---
// 1. Pedido debe estar 'Listo para recoger'.
// 2. Pedido no tiene repartidor asignado.
// 3. EL REPARTIDOR DEBE ESTAR AFILIADO Y APROBADO POR EL RESTAURANTE (Nueva seguridad).
// 4. El repartidor no debe haber postulado ya a este pedido.

$sql_pedidos = "SELECT p.id, p.direccion_pedido, r.nombre_restaurante, r.direccion as direccion_restaurante
                FROM pedidos p
                JOIN restaurantes r ON p.id_restaurante = r.id
                JOIN repartidor_afiliaciones ra ON p.id_restaurante = ra.id_restaurante
                WHERE p.estado_pedido = 'Listo para recoger' 
                  AND p.id_repartidor IS NULL
                  AND ra.id_repartidor = ? 
                  AND ra.estado_afiliacion = 'aprobado'
                  AND NOT EXISTS (
                      SELECT 1 FROM pedido_solicitudes_entrega pse
                      WHERE pse.id_pedido = p.id AND pse.id_repartidor = ?
                  )";

$stmt_pedidos = $conn->prepare($sql_pedidos);

// Vinculamos el ID dos veces: 
// 1. Para verificar que ESTE repartidor está afiliado (ra.id_repartidor).
// 2. Para verificar que ESTE repartidor no ha postulado ya (pse.id_repartidor).
$stmt_pedidos->bind_param("ii", $id_repartidor, $id_repartidor);

$stmt_pedidos->execute();
$resultado_pedidos = $stmt_pedidos->get_result();
?>

<div class="row g-4">
<?php if ($resultado_pedidos->num_rows > 0):
    while($pedido = $resultado_pedidos->fetch_assoc()): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card delivery-job-card h-100 shadow-sm">
            <div class="card-header bg-white text-center">
                <h5 class="mb-0 fw-bold">Pedido #<?php echo $pedido['id']; ?></h5>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="route-info flex-grow-1">
                    <div class="route-point pickup">
                        <i class="bi bi-shop icon"></i>
                        <div>
                            <small class="text-muted">RECOGER EN</small>
                            <strong><?php echo htmlspecialchars($pedido['nombre_restaurante']); ?></strong>
                        </div>
                    </div>
                    <div class="route-line"></div>
                    <div class="route-point dropoff">
                        <i class="bi bi-house-door-fill icon"></i>
                        <div>
                            <small class="text-muted">ENTREGAR EN</small>
                            <strong><?php echo htmlspecialchars($pedido['direccion_pedido']); ?></strong>
                        </div>
                    </div>
                </div>
                <form action="../procesos/solicitar_entrega.php" method="POST" class="mt-4">
                    <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                    <button type="submit" class="btn btn-success w-100 fw-bold">¡Quiero llevarlo!</button>
                </form>
            </div>
        </div>
    </div>
    <?php endwhile;
else: ?>
    <div class="col-12">
        <div class="text-center p-5 bg-light rounded-3">
            <img src="../assets/img/no-orders-repartidor.png" alt="Sin pedidos" style="width: 180px;" class="mb-3">
            <h4 class="fw-bold">No tienes pedidos disponibles</h4>
            <p class="text-muted">
                No hay pedidos de tus restaurantes afiliados.<br>
                <small>Recuerda: Solo verás pedidos de restaurantes que hayan <strong>aprobado</strong> tu solicitud de afiliación.</small>
            </p>
        </div>
    </div>
<?php endif; ?>
</div>

<?php
$stmt_pedidos->close();
$conn->close();
?>