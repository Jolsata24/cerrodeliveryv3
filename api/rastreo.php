<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once '../includes/conexion.php';

$id_pedido = $_GET['id_pedido'] ?? null;

if (!$id_pedido) {
    echo json_encode(["success" => false, "message" => "Falta ID pedido"]);
    exit();
}

// 1. OBTENER ESTADO DEL PEDIDO Y ID REPARTIDOR
$sql = "SELECT p.estado, p.repartidor_id, p.latitud as lat_destino, p.longitud as lon_destino,
               r.nombre as nombre_repartidor, r.telefono as tel_repartidor,
               r.latitud as lat_rep, r.longitud as lon_rep
        FROM pedidos p
        LEFT JOIN usuarios_repartidores r ON p.repartidor_id = r.id
        WHERE p.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_pedido);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    // Si no hay repartidor asignado aún
    if (!$row['repartidor_id']) {
        $row['nombre_repartidor'] = "Buscando repartidor...";
        $row['lat_rep'] = null;
    }
    
    echo json_encode(["success" => true, "data" => $row]);
} else {
    echo json_encode(["success" => false, "message" => "Pedido no encontrado"]);
}
?>