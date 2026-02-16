<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once '../includes/conexion.php';

$id_pedido = $_GET['id_pedido'] ?? null;

if (!$id_pedido) {
    echo json_encode([]);
    exit;
}

try {
    // Consultamos los detalles usando tu tabla existente 'detalle_pedidos'
    $sql = "SELECT cantidad, nombre_plato, precio_unitario 
            FROM detalle_pedidos 
            WHERE id_pedido = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_pedido);
    $stmt->execute();
    $result = $stmt->get_result();

    $detalles = [];
    while ($row = $result->fetch_assoc()) {
        $detalles[] = $row;
    }

    echo json_encode($detalles);

} catch (Exception $e) {
    echo json_encode([]);
}
?>