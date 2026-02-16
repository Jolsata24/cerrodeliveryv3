<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad: solo restaurantes logueados
if (!isset($_SESSION['restaurante_id'])) {
    die("Acceso denegado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_restaurante = $_SESSION['restaurante_id'];
    $telefono = trim($_POST['telefono']);

    // Preparamos la consulta para actualizar el teléfono
    $sql = "UPDATE restaurantes SET telefono = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $telefono, $id_restaurante);

    if ($stmt->execute()) {
        header("Location: ../restaurante/dashboard.php?status=telefono_actualizado");
    } else {
        die("Error al actualizar el teléfono: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
}
?>