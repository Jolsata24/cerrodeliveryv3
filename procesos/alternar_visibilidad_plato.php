<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad: solo el restaurante logueado puede cambiar sus platos
if (!isset($_SESSION['restaurante_id'])) {
    die("Acceso denegado.");
}

// Verificamos que nos envíen el ID del plato
if (!isset($_GET['id_plato']) || !is_numeric($_GET['id_plato'])) {
    header("Location: ../restaurante/dashboard.php?error=id_invalido");
    exit();
}

$id_plato = $_GET['id_plato'];
$id_restaurante = $_SESSION['restaurante_id'];

// Esta consulta es la magia:
// Actualiza el plato, invirtiendo el valor de 'esta_visible' (de 1 a 0, o de 0 a 1)
// Se asegura de que el plato pertenezca al restaurante (por seguridad)
$sql = "UPDATE menu_platos 
        SET esta_visible = NOT esta_visible 
        WHERE id = ? AND id_restaurante = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_plato, $id_restaurante);

if ($stmt->execute()) {
    // Si todo sale bien, volvemos al dashboard
    header("Location: ../restaurante/dashboard.php?status=plato_actualizado");
} else {
    die("Error al actualizar la visibilidad del plato: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>