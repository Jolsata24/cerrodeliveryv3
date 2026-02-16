<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad
if (!isset($_SESSION['restaurante_id'])) {
    die("Acceso denegado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_restaurante = $_SESSION['restaurante_id'];
    $latitud = $_POST['latitud'];
    $longitud = $_POST['longitud'];

    $sql = "UPDATE restaurantes SET latitud = ?, longitud = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ddi", $latitud, $longitud, $id_restaurante);

    if ($stmt->execute()) {
        header("Location: ../restaurante/dashboard.php?status=ubicacion_actualizada");
    } else {
        die("Error al actualizar ubicación: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
}
?>