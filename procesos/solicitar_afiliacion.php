<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad: solo repartidores logueados
if (!isset($_SESSION['repartidor_id'])) {
    die("Acceso no autorizado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_restaurante'])) {
    
    $id_repartidor = $_SESSION['repartidor_id'];
    $id_restaurante = $_POST['id_restaurante'];

    // El estado por defecto es 'pendiente' gracias a la estructura de la tabla
    $sql = "INSERT INTO repartidor_afiliaciones (id_repartidor, id_restaurante) VALUES (?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_repartidor, $id_restaurante);
    
    if ($stmt->execute()) {
        header("Location: ../repartidor/dashboard.php?status=solicitud_enviada");
    } else {
        // El error 1062 es para entradas duplicadas (si ya existe la solicitud)
        if ($conn->errno == 1062) {
             header("Location: ../repartidor/dashboard.php?error=solicitud_existente");
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