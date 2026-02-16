<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad: solo restaurantes logueados pueden hacer esto
if (!isset($_SESSION['restaurante_id'])) {
    die("Acceso denegado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pedido = $_POST['id_pedido'];
    $nuevo_estado = $_POST['nuevo_estado'];
    $id_restaurante_sesion = $_SESSION['restaurante_id'];

    // Consulta para actualizar el estado, asegurándonos de que el pedido
    // realmente pertenece al restaurante que está logueado (¡medida de seguridad!)
    $sql = "UPDATE pedidos SET estado_pedido = ? WHERE id = ? AND id_restaurante = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $nuevo_estado, $id_pedido, $id_restaurante_sesion);

    if ($stmt->execute()) {
        // Si se actualizó, redirigir de vuelta a la página de pedidos
        header("Location: ../restaurante/pedidos.php?status=actualizado");
        exit();
    } else {
        die("Error al actualizar el estado del pedido.");
    }
    $stmt->close();
    $conn->close();
}
?>