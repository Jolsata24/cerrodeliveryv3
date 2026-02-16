<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad: solo restaurantes logueados
if (!isset($_SESSION['restaurante_id'])) {
    die("Acceso denegado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_afiliacion']) && isset($_POST['accion'])) {
    
    $id_afiliacion = $_POST['id_afiliacion'];
    $accion = $_POST['accion'];
    $id_restaurante = $_SESSION['restaurante_id'];

    // Determinar el nuevo estado
    if ($accion == 'aprobar') {
        $nuevo_estado = 'aprobado';
    } else if ($accion == 'rechazar') {
        $nuevo_estado = 'rechazado';
    } else {
        die("Acción no válida.");
    }

    // Actualizamos el estado, asegurándonos de que el restaurante logueado sea el dueño de la afiliación
    $sql = "UPDATE repartidor_afiliaciones SET estado_afiliacion = ? WHERE id = ? AND id_restaurante = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $nuevo_estado, $id_afiliacion, $id_restaurante);
    
    if ($stmt->execute()) {
        header("Location: ../restaurante/dashboard.php?status=afiliacion_gestionada");
    } else {
        die("Error al gestionar la afiliación: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
} else {
    header("Location: ../restaurante/dashboard.php");
    exit();
}
?>