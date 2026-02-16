<?php
session_start();
require_once '../includes/conexion.php';

if (!isset($_SESSION['repartidor_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    exit();
}

$id_repartidor = $_SESSION['repartidor_id'];
$data = json_decode(file_get_contents('php://input'), true);
$id_solicitud = $data['id_solicitud'];

if (empty($id_solicitud)) {
    http_response_code(400);
    exit();
}

// Actualizamos la columna notificacion_vista a 1 (verdadero)
$sql = "UPDATE pedido_solicitudes_entrega 
        SET notificacion_vista = 1 
        WHERE id = ? AND id_repartidor = ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_solicitud, $id_repartidor);
$stmt->execute();

$stmt->close();
$conn->close();

// Respondemos con éxito
http_response_code(200);
echo json_encode(['status' => 'success']);
?>