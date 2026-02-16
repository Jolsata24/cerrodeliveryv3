<?php
session_start();
require_once '../includes/conexion.php';
header('Content-Type: application/json');

// Seguridad: solo clientes logueados pueden consultar
if (!isset($_SESSION['cliente_id']) || !isset($_GET['id_repartidor'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado.']);
    exit;
}

$id_repartidor = $_GET['id_repartidor'];

$sql = "SELECT latitud, longitud FROM repartidor_ubicaciones WHERE id_repartidor = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_repartidor);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $ubicacion = $resultado->fetch_assoc();
    echo json_encode([
        'status' => 'success',
        'latitud' => $ubicacion['latitud'],
        'longitud' => $ubicacion['longitud']
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ubicación del repartidor no encontrada.']);
}

$stmt->close();
$conn->close();
?>