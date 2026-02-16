<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad: solo repartidores logueados pueden aceptar pedidos
if (!isset($_SESSION['repartidor_id'])) {
    die("Acceso no autorizado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pedido = $_POST['id_pedido'];
    $id_repartidor = $_SESSION['repartidor_id'];
    $nuevo_estado_pedido = 'En camino';

    // Para evitar que dos repartidores tomen el mismo pedido,
    // nos aseguramos de que el pedido todavía esté disponible.
    $sql = "UPDATE pedidos 
            SET id_repartidor = ?, estado_pedido = ? 
            WHERE id = ? AND id_repartidor IS NULL AND estado_pedido = 'Listo para recoger'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $id_repartidor, $nuevo_estado_pedido, $id_pedido);

    if ($stmt->execute()) {
        // Verificamos si la fila fue afectada.
        // Si affected_rows es 0, significa que otro repartidor tomó el pedido justo antes.
        if ($stmt->affected_rows > 0) {
            // ¡Éxito! El pedido ha sido asignado.
            // Redirigir a una página de detalles del pedido para el repartidor (futura mejora)
            header("Location: ../repartidor/mis_entregas.php?status=aceptado");
            exit();
        } else {
            // El pedido ya no estaba disponible
            header("Location: ../repartidor/dashboard.php?error=pedido_no_disponible");
            exit();
        }
    } else {
        die("Error al aceptar el pedido: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
}
?>