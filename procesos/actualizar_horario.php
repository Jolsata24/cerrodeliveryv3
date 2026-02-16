<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad: solo restaurantes logueados
if (!isset($_SESSION['restaurante_id'])) {
    die("Acceso denegado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_restaurante = $_SESSION['restaurante_id'];
    // Recoger y validar que los datos no estén vacíos
    $hora_apertura = !empty($_POST['hora_apertura']) ? $_POST['hora_apertura'] : null;
    $hora_cierre = !empty($_POST['hora_cierre']) ? $_POST['hora_cierre'] : null;

    // Preparamos la consulta para actualizar el horario
    $sql = "UPDATE restaurantes SET hora_apertura = ?, hora_cierre = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $hora_apertura, $hora_cierre, $id_restaurante);

    if ($stmt->execute()) {
        // Redirigir de vuelta al dashboard con un mensaje de éxito
        header("Location: ../restaurante/dashboard.php?status=horario_actualizado");
    } else {
        die("Error al actualizar el horario: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
}
?>