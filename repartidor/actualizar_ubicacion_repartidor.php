<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad: solo repartidores logueados
if (!isset($_SESSION['repartidor_id'])) {
    http_response_code(403); // Forbidden
    die("Acceso no autorizado.");
}

// Leer los datos JSON enviados desde el JavaScript
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['lat']) && isset($data['lon'])) {
    $id_repartidor = $_SESSION['repartidor_id'];
    $latitud = $data['lat'];
    $longitud = $data['lon'];

    // Usamos INSERT ... ON DUPLICATE KEY UPDATE para crear o actualizar la fila
    $sql = "INSERT INTO repartidor_ubicaciones (id_repartidor, latitud, longitud) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE latitud = ?, longitud = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idddd", $id_repartidor, $latitud, $longitud, $latitud, $longitud);
    
    if ($stmt->execute()) {
        http_response_code(200); // OK
        echo json_encode(["status" => "success"]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "error", "message" => "Datos incompletos."]);
}
?>