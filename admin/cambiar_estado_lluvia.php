<?php
session_start();
require_once '../includes/conexion.php';

// CORRECCIÓN: Verificamos 'admin_logged_in' que es lo que usa tu sistema
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'msg' => 'No autorizado']);
    exit;
}

$nuevo_estado = $_POST['estado'] ?? '0'; // Recibe '1' o '0'

// Actualizamos en la base de datos
$sql = "UPDATE configuracion SET valor = ? WHERE clave = 'modo_lluvia'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nuevo_estado);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'nuevo_estado' => $nuevo_estado]);
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Error SQL: ' . $conn->error]);
}
?>