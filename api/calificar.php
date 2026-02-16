<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once '../includes/conexion.php';

$input = json_decode(file_get_contents('php://input'), true);
$id_cliente     = $input['id_cliente'] ?? null;
$id_restaurante = $input['id_restaurante'] ?? null;
$puntuacion     = $input['puntuacion'] ?? null;

if (!$id_cliente || !$id_restaurante || !$puntuacion) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
    exit;
}

// 1. VERIFICAR SI YA VOTÓ (ESTRICTO)
$sql_check = "SELECT id FROM restaurante_puntuaciones WHERE id_restaurante = ? AND id_cliente = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $id_restaurante, $id_cliente);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    // YA EXISTE -> BLOQUEAMOS
    echo json_encode(['status' => 'error', 'message' => '¡Ya calificaste este restaurante anteriormente!']);
    $stmt_check->close();
    exit;
}
$stmt_check->close();

// SI NO EXISTE, CONTINUAMOS...
$conn->begin_transaction();

try {
    // 2. Insertar nuevo voto
    $sql_insert = "INSERT INTO restaurante_puntuaciones (id_restaurante, id_cliente, puntuacion) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("iii", $id_restaurante, $id_cliente, $puntuacion);
    $stmt->execute();

    // 3. Recalcular Promedio
    $sql_recalc = "SELECT AVG(puntuacion) as promedio, COUNT(id) as total FROM restaurante_puntuaciones WHERE id_restaurante = ?";
    $stmt_calc = $conn->prepare($sql_recalc);
    $stmt_calc->bind_param("i", $id_restaurante);
    $stmt_calc->execute();
    $res = $stmt_calc->get_result()->fetch_assoc();
    
    // 4. Actualizar Restaurante
    $sql_update = "UPDATE restaurantes SET puntuacion_promedio = ?, total_puntuaciones = ? WHERE id = ?";
    $stmt_upd = $conn->prepare($sql_update);
    $stmt_upd->bind_param("dii", $res['promedio'], $res['total'], $id_restaurante);
    $stmt_upd->execute();

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => '¡Gracias por tu calificación!']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Error al guardar']);
}
?>