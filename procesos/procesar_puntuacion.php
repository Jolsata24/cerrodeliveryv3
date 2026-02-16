<?php
session_start();
require_once '../includes/conexion.php';
header('Content-Type: application/json'); // Es crucial para que JavaScript entienda la respuesta

// Seguridad: solo clientes logueados pueden votar
if (!isset($_SESSION['cliente_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Debes iniciar sesión para votar.']);
    exit;
}

// Leer los datos JSON que envía el JavaScript
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_restaurante']) || !isset($data['puntuacion'])) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos.']);
    exit;
}

$id_cliente = $_SESSION['cliente_id'];
$id_restaurante = $data['id_restaurante'];
$puntuacion = $data['puntuacion'];

$conn->begin_transaction();

try {
    // Inserta un nuevo voto o actualiza el existente si el usuario ya votó por este restaurante.
    $sql_insert = "INSERT INTO restaurante_puntuaciones (id_restaurante, id_cliente, puntuacion) 
                   VALUES (?, ?, ?)
                   ON DUPLICATE KEY UPDATE puntuacion = ?";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iiii", $id_restaurante, $id_cliente, $puntuacion, $puntuacion);
    $stmt_insert->execute();

    // Recalcula el promedio y el total de votos para el restaurante
    $sql_recalc = "SELECT AVG(puntuacion) as promedio, COUNT(id) as total FROM restaurante_puntuaciones WHERE id_restaurante = ?";
    $stmt_recalc = $conn->prepare($sql_recalc);
    $stmt_recalc->bind_param("i", $id_restaurante);
    $stmt_recalc->execute();
    $resultado = $stmt_recalc->get_result()->fetch_assoc();
    
    $nuevo_promedio = $resultado['promedio'];
    $nuevo_total = $resultado['total'];

    // Actualiza la tabla de restaurantes con los nuevos datos
    $sql_update = "UPDATE restaurantes SET puntuacion_promedio = ?, total_puntuaciones = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("dii", $nuevo_promedio, $nuevo_total, $id_restaurante);
    $stmt_update->execute();

    // Si todo fue exitoso, guarda los cambios
    $conn->commit();

    // Devuelve una respuesta de éxito al JavaScript
    echo json_encode([
        'status' => 'success',
        'message' => '¡Gracias por tu voto!'
    ]);

} catch (Exception $e) {
    // Si algo falló, revierte todos los cambios
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Error al procesar la puntuación.']);
}
?>