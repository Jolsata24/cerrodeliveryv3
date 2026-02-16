<?php
require_once '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $telefono = trim($_POST['telefono']);

    // Encriptar la contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // El estado de aprobación por defecto es 'pendiente'
    $sql = "INSERT INTO repartidores (nombre, email, password, telefono, estado_aprobacion) VALUES (?, ?, ?, ?, 'pendiente')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nombre, $email, $password_hash, $telefono);
    
    if ($stmt->execute()) {
        // Redirigir a una página de agradecimiento o al login
        header("Location: ../login_repartidor.php?status=solicitud_enviada");
        exit();
    } else {
        die("Error al enviar la solicitud: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
}
?>