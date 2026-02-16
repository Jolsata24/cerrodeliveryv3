<?php
// Desactivar impresi車n de errores HTML para no romper el JSON
error_reporting(0);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../includes/conexion.php';
$conn->set_charset("utf8");

// Validar conexi車n
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error BD: ' . $conn->connect_error]);
    exit;
}

$accion = $_REQUEST['accion'] ?? '';
$id_cliente = $_REQUEST['id_cliente'] ?? 0;

// --- LISTAR ---
if ($accion == 'listar') {
    $sql = "SELECT * FROM cliente_direcciones WHERE id_cliente = ? ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_cliente);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $data = [];
    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $data]);
}

// --- GUARDAR ---
elseif ($accion == 'guardar') {
    $nombre = $_POST['nombre'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $lat = $_POST['latitud'] ?? '0';
    $lng = $_POST['longitud'] ?? '0';
    $ref = $_POST['referencia'] ?? '';

    // Si la direcci車n viene vac赤a o con solo comas, la rechazamos
    if (empty($direccion) || $direccion == ', ') {
        echo json_encode(['success' => false, 'message' => 'Direcci車n inv芍lida']);
        exit;
    }

    $sql = "INSERT INTO cliente_direcciones (id_cliente, nombre, direccion, referencia, latitud, longitud) 
            VALUES (?, ?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("isssss", $id_cliente, $nombre, $direccion, $ref, $lat, $lng);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Guardado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error Execute: ' . $stmt->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error Prepare: ' . $conn->error]);
    }
}

// --- BORRAR ---
elseif ($accion == 'borrar') {
    $id_dir = $_POST['id_direccion'] ?? 0;
    $sql = "DELETE FROM cliente_direcciones WHERE id = ? AND id_cliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_dir, $id_cliente);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Sin acci車n']);
}
?>