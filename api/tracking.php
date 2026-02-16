<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once '../includes/conexion.php';

$id_pedido = $_GET['id_pedido'] ?? null;

if (!$id_pedido) {
    echo json_encode(['success' => false, 'message' => 'Falta ID pedido']);
    exit;
}

try {
    // 1. Obtener datos del pedido y destino (Casa del cliente)
    $sql_pedido = "SELECT p.estado_pedido, p.id_repartidor, p.latitud as lat_dest, p.longitud as lng_dest 
                   FROM pedidos p WHERE p.id = ?";
    $stmt = $conn->prepare($sql_pedido);
    $stmt->bind_param("i", $id_pedido);
    $stmt->execute();
    $res_pedido = $stmt->get_result()->fetch_assoc();

    if (!$res_pedido) {
        echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
        exit;
    }

    $response = [
        'success' => true,
        'estado'  => $res_pedido['estado_pedido'],
        'destino' => [
            'lat' => $res_pedido['lat_dest'],
            'lng' => $res_pedido['lng_dest']
        ],
        'asignado' => false,
        'repartidor' => null
    ];

    // 2. Si hay repartidor asignado, buscamos su ubicación en tiempo real
    if ($res_pedido['id_repartidor']) {
        $id_repartidor = $res_pedido['id_repartidor'];
        
        // Hacemos JOIN entre datos del repartidor y su ubicación en vivo
        $sql_rep = "SELECT r.nombre, r.telefono, ru.latitud, ru.longitud 
                    FROM repartidores r
                    LEFT JOIN repartidor_ubicaciones ru ON r.id = ru.id_repartidor
                    WHERE r.id = ?";
        
        $stmt_rep = $conn->prepare($sql_rep);
        $stmt_rep->bind_param("i", $id_repartidor);
        $stmt_rep->execute();
        $res_rep = $stmt_rep->get_result()->fetch_assoc();

        if ($res_rep) {
            $response['asignado'] = true;
            $response['repartidor'] = [
                'nombre'   => $res_rep['nombre'],
                'telefono' => $res_rep['telefono'],
                // Si latitud es null (aún no se conectó), enviamos 0 o null
                'lat'      => $res_rep['latitud'] ?? 0, 
                'lng'      => $res_rep['longitud'] ?? 0
            ];
        }
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error servidor']);
}
?>