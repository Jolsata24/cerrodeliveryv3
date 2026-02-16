<?php
require_once '../includes/conexion.php';
header('Content-Type: application/json');

if (isset($_GET['id_restaurante'])) {
    $id = $_GET['id_restaurante'];
    
    // AGREGAMOS latitud y longitud a la consulta
    $sql = "SELECT nombre_restaurante, yape_numero, yape_qr, latitud, longitud FROM restaurantes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['status' => 'success', 'data' => $row]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Restaurante no encontrado']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Falta ID']);
}
?>