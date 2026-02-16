<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad: solo repartidores logueados
if (!isset($_SESSION['repartidor_id'])) {
    die("Acceso no autorizado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_pedido'])) {
    
    $id_repartidor = $_SESSION['repartidor_id'];
    $id_pedido = $_POST['id_pedido'];

    // Insertamos la solicitud en la nueva tabla. El estado por defecto es 'pendiente'.
    $sql = "INSERT INTO pedido_solicitudes_entrega (id_pedido, id_repartidor) VALUES (?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_pedido, $id_repartidor);
    
    if ($stmt->execute()) {
        header("Location: ../repartidor/dashboard.php?status=solicitud_exitosa");
    } else {
        // Error 1062 es para 'solicitud_unica' (si ya solicitó este pedido)
        if ($conn->errno == 1062) {
             header("Location: ../repartidor/dashboard.php?error=ya_solicitado");
        } else {
            die("Error al enviar la solicitud: " . $stmt->error);
        }
    }
    
    $stmt->close();
    $conn->close();
} else {
    header("Location: ../repartidor/dashboard.php");
    exit();
}
?>