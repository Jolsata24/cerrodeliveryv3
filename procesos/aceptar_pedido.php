<?php
session_start();
require_once '../includes/conexion.php';

if (!isset($_SESSION['repartidor_id'])) {
    die("Acceso denegado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pedido = $_POST['id_pedido'];
    $id_repartidor = $_SESSION['repartidor_id'];

    // Se asigna el repartidor y se cambia el estado a 'En preparación'
    $sql = "UPDATE pedidos SET id_repartidor = ?, estado_pedido = 'En preparación' WHERE id = ? AND id_repartidor IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_repartidor, $id_pedido);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header("Location: ../repartidor/mis_entregas.php?msg=pedido_aceptado");
    } else {
        die("Error: El pedido ya fue tomado por otro repartidor o no existe.");
    }
    
    $stmt->close();
    $conn->close();
}
?>