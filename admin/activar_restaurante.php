<?php
session_start();
// Seguridad: solo admins pueden ejecutar esto
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../includes/conexion.php';

// Validamos que el ID del restaurante venga en la URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Error: ID no proporcionado o inválido.");
}

$id_restaurante = $_GET['id'];

// Calculamos la nueva fecha de vencimiento: hoy + 30 días
$fecha_vencimiento = date('Y-m-d', strtotime('+30 days'));

// Preparamos la consulta para actualizar el estado y la fecha
$sql = "UPDATE restaurantes SET estado = 'activo', fecha_vencimiento_suscripcion = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $fecha_vencimiento, $id_restaurante);

// Ejecutamos y redirigimos de vuelta al dashboard
if ($stmt->execute()) {
    header("Location: dashboard.php?status=activado");
} else {
    die("Error al activar el restaurante: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>