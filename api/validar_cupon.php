<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once '../includes/conexion.php';

$codigo = $_GET['codigo'] ?? '';

if (empty($codigo)) {
    echo json_encode(['success' => false, 'message' => 'Escribe un código']);
    exit;
}

// Buscar el cupón activo y que no haya vencido
$sql = "SELECT * FROM cupones 
        WHERE codigo = ? 
        AND activo = 1 
        AND fecha_limite >= NOW() 
        AND usos_actuales < usos_maximos";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $codigo);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    echo json_encode([
        'success' => true,
        'message' => '¡Cupón aplicado!',
        'tipo'    => $row['tipo'],   // 'fijo' o 'porcentaje'
        'valor'   => (float)$row['valor']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Cupón inválido o expirado']);
}
?>