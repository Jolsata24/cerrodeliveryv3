<?php
session_start();
// Seguridad
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require_once '../includes/conexion.php';

$id_repartidor = $_GET['id'];
if (!is_numeric($id_repartidor)) { die("ID inválido."); }

// Actualizar el estado a 'aprobado'
$sql = "UPDATE repartidores SET estado_aprobacion = 'aprobado' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_repartidor);

if ($stmt->execute()) {
    header("Location: dashboard.php?status=repartidor_aprobado");
} else {
    die("Error al aprobar al repartidor: " . $stmt->error);
}
$stmt->close();
$conn->close();
?>