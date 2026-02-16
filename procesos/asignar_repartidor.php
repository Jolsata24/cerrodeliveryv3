<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad: solo restaurantes logueados
if (!isset($_SESSION['restaurante_id'])) {
    die("Acceso denegado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_pedido']) && isset($_POST['id_repartidor'])) {
    
    $id_pedido = $_POST['id_pedido'];
    $id_repartidor_elegido = $_POST['id_repartidor'];
    $id_restaurante = $_SESSION['restaurante_id'];

    // --- INICIAMOS UNA TRANSACCIÓN ---
    // Esto asegura que todas las operaciones se completen con éxito, o ninguna lo hará.
    $conn->begin_transaction();

    try {
        // --- LA LÍNEA CRÍTICA CORREGIDA ---
        // Se cambió la condición 'AND id_repartidor IS NULL' por 'AND estado_pedido = 'Listo para recoger''.
        // Esto es más robusto y se asegura de que solo se puedan asignar repartidores a pedidos que están en el estado correcto.
        $sql1 = "UPDATE pedidos SET id_repartidor = ?, estado_pedido = 'En camino' 
                 WHERE id = ? AND id_restaurante = ? AND estado_pedido = 'Listo para recoger'";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("iii", $id_repartidor_elegido, $id_pedido, $id_restaurante);
        $stmt1->execute();

        // Si la consulta anterior no afectó a ninguna fila (porque otro admin lo asignó, o el estado no era el correcto), detenemos.
        if ($stmt1->affected_rows === 0) {
            throw new Exception("El pedido no estaba disponible para asignación. Es posible que ya haya sido asignado.");
        }

        // 2. Marcar la solicitud del repartidor elegido como 'aprobado'
        $sql2 = "UPDATE pedido_solicitudes_entrega SET estado_solicitud = 'aprobado' 
                 WHERE id_pedido = ? AND id_repartidor = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("ii", $id_pedido, $id_repartidor_elegido);
        $stmt2->execute();

        // 3. Rechazar todas las OTRAS solicitudes pendientes para este pedido
        $sql3 = "UPDATE pedido_solicitudes_entrega SET estado_solicitud = 'rechazado' 
                 WHERE id_pedido = ? AND id_repartidor != ?";
        $stmt3 = $conn->prepare($sql3);
        $stmt3->bind_param("ii", $id_pedido, $id_repartidor_elegido);
        $stmt3->execute();

        // Si todo salió bien, guardamos los cambios
        $conn->commit();

        header("Location: ../restaurante/pedidos.php?status=repartidor_asignado");
        exit();

    } catch (Exception $e) {
        // Si algo falla, revertimos todos los cambios
        $conn->rollback();
        die("Error al asignar el repartidor: " . $e->getMessage());
    }

} else {
    header("Location: ../restaurante/pedidos.php");
    exit();
}
?>