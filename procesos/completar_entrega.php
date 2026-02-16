<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad: solo repartidores logueados pueden completar entregas
if (!isset($_SESSION['repartidor_id'])) {
    die("Acceso no autorizado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pedido = $_POST['id_pedido'];
    $id_repartidor = $_SESSION['repartidor_id'];
    $nuevo_estado = 'Entregado';

    // Actualizamos el estado del pedido, asegurándonos de que
    // el repartidor que lo completa es el que lo tiene asignado.
    $sql = "UPDATE pedidos 
            SET estado_pedido = ? 
            WHERE id = ? AND id_repartidor = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $nuevo_estado, $id_pedido, $id_repartidor);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Éxito. El pedido se marcó como entregado.
            // Lo devolvemos a su página de entregas activas.
            header("Location: ../repartidor/mis_entregas.php?status=entregado");
            exit();
        } else {
            // No se afectaron filas, probablemente porque el pedido no le pertenecía.
            header("Location: ../repartidor/mis_entregas.php?error=no_autorizado");
            exit();
        }
    } else {
        die("Error al completar la entrega: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
}
?>