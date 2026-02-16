<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad
if (!isset($_SESSION['restaurante_id']) || !isset($_GET['id_pedido'])) {
    die();
}
$id_restaurante = $_SESSION['restaurante_id'];
$id_pedido = $_GET['id_pedido'];

// Sub-consulta para obtener los repartidores que solicitaron ESTE pedido
$sql_solicitudes = "SELECT pse.id_repartidor, r.nombre 
                    FROM pedido_solicitudes_entrega pse
                    JOIN repartidores r ON pse.id_repartidor = r.id
                    JOIN pedidos p ON pse.id_pedido = p.id
                    WHERE pse.id_pedido = ? 
                    AND p.id_restaurante = ?
                    AND pse.estado_solicitud = 'pendiente'";
$stmt_solicitudes = $conn->prepare($sql_solicitudes);
$stmt_solicitudes->bind_param("ii", $id_pedido, $id_restaurante);
$stmt_solicitudes->execute();
$resultado_solicitudes = $stmt_solicitudes->get_result();

// --- Generar el HTML de respuesta ---
if ($resultado_solicitudes->num_rows > 0): ?>
    <ul class="list-group">
        <?php while($solicitud = $resultado_solicitudes->fetch_assoc()): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <?php echo htmlspecialchars($solicitud['nombre']); ?>
            <form action="../procesos/asignar_repartidor.php" method="POST">
                <input type="hidden" name="id_pedido" value="<?php echo $id_pedido; ?>">
                <input type="hidden" name="id_repartidor" value="<?php echo $solicitud['id_repartidor']; ?>">
                <button type="submit" class="btn btn-success btn-sm">Asignar a este repartidor</button>
            </form>
        </li>
        <?php endwhile; ?>
    </ul>
<?php else: ?>
    <p class="text-muted">Esperando solicitudes de repartidores...</p>
<?php endif;

$stmt_solicitudes->close();
$conn->close();
?>