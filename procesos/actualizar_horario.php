<?php
session_start();
require_once '../includes/conexion.php';

if (!isset($_SESSION['restaurante_id'])) {
    header('Location: ../login_restaurante.php');
    exit();
}

$id_restaurante = $_SESSION['restaurante_id'];

// Recibir datos (si envían vacío, guardamos NULL)
$hora_apertura = !empty($_POST['hora_apertura']) ? $_POST['hora_apertura'] : null;
$hora_cierre = !empty($_POST['hora_cierre']) ? $_POST['hora_cierre'] : null;
$hora_apertura_sab = !empty($_POST['hora_apertura_sab']) ? $_POST['hora_apertura_sab'] : null;
$hora_cierre_sab = !empty($_POST['hora_cierre_sab']) ? $_POST['hora_cierre_sab'] : null;
$hora_apertura_dom = !empty($_POST['hora_apertura_dom']) ? $_POST['hora_apertura_dom'] : null;
$hora_cierre_dom = !empty($_POST['hora_cierre_dom']) ? $_POST['hora_cierre_dom'] : null;

$sql = "UPDATE restaurantes SET 
        hora_apertura = ?, hora_cierre = ?, 
        hora_apertura_sab = ?, hora_cierre_sab = ?, 
        hora_apertura_dom = ?, hora_cierre_dom = ? 
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssi", $hora_apertura, $hora_cierre, $hora_apertura_sab, $hora_cierre_sab, $hora_apertura_dom, $hora_cierre_dom, $id_restaurante);

if ($stmt->execute()) {
    header('Location: ../restaurante/dashboard.php?msg=horario_actualizado');
} else {
    header('Location: ../restaurante/dashboard.php?error=actualizacion_fallida');
}

$stmt->close();
$conn->close();
?>