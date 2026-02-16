<?php
session_start();
require_once '../includes/conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['repartidor_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit();
}
$id_repartidor = $_SESSION['repartidor_id'];

// Buscamos solicitudes aprobadas cuya notificación aún no ha sido vista por el repartidor
$sql = "SELECT id FROM pedido_solicitudes_entrega 
        WHERE id_repartidor = ? 
        AND estado_solicitud = 'aprobado' 
        AND notificacion_vista = 0 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_repartidor);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $solicitud = $resultado->fetch_assoc();
    // ¡Encontramos una! Enviamos la señal de "nueva_entrega" y el ID de la solicitud
    echo json_encode(['status' => 'nueva_entrega', 'id_solicitud' => $solicitud['id']]);
} else {
    // No hay nada nuevo
    echo json_encode(['status' => 'sin_cambios']);
}

$stmt->close();
$conn->close();
?>