<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad: solo repartidores logueados pueden aceptar pedidos
if (!isset($_SESSION['repartidor_id'])) {
    die("Acceso denegado. Solo un repartidor puede tomar el pedido.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pedido = $_POST['id_pedido'];
    $id_repartidor = $_SESSION['repartidor_id'];
    
    // Al aceptar el pedido, el nuevo estado automático debe ser 'En preparación' para avisarle al restaurante que cocine
    $nuevo_estado_pedido = 'En preparación'; 

    // La consulta clave: buscamos el pedido 'Pendiente' (antes decía 'Listo para recoger')
    $sql = "UPDATE pedidos 
            SET id_repartidor = ?, estado_pedido = ? 
            WHERE id = ? AND id_repartidor IS NULL AND estado_pedido = 'Pendiente'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $id_repartidor, $nuevo_estado_pedido, $id_pedido);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Éxito: el pedido fue tomado, mandamos al repartidor a su mapa de entregas
            header("Location: ../repartidor/mis_entregas.php?status=aceptado");
            exit();
        } else {
            // Alguien más lo tomó primero o el pedido ya no está disponible
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