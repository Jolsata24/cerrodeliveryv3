<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad Modificada: Ahora AMBOS (Restaurante o Repartidor) pueden acceder a este archivo
if (!isset($_SESSION['restaurante_id']) && !isset($_SESSION['repartidor_id'])) {
    die("Acceso denegado. No tienes permisos para actualizar el estado del pedido.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pedido = $_POST['id_pedido'];
    $nuevo_estado = $_POST['nuevo_estado'];

    // Actualizamos el estado del pedido
    $sql = "UPDATE pedidos SET estado_pedido = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevo_estado, $id_pedido);

    if ($stmt->execute()) {
        // Redirección inteligente dependiendo de quién hizo el cambio
        if (isset($_SESSION['repartidor_id'])) {
            // Si fue el motorizado, lo devolvemos a su panel de viajes
            header("Location: ../repartidor/mis_entregas.php?msg=estado_actualizado");
        } else {
            // Si fue el restaurante, lo devolvemos a su panel
            header("Location: ../restaurante/pedidos.php?msg=estado_actualizado");
        }
        exit();
    } else {
        die("Error al actualizar estado: " . $conn->error);
    }
    
    $stmt->close();
    $conn->close();
} else {
    header("Location: ../index.php");
    exit();
}
?>