<?php
session_start();
require_once '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Buscar al repartidor por email
    $sql = "SELECT id, nombre, password, estado_aprobacion FROM repartidores WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $repartidor = $result->fetch_assoc();
        
        // Verificar contraseña
        if (password_verify($password, $repartidor['password'])) {
            
            // ¡VERIFICACIÓN CRUCIAL! ¿Está aprobado?
            if ($repartidor['estado_aprobacion'] == 'aprobado') {
                // Login exitoso
                $_SESSION['repartidor_id'] = $repartidor['id'];
                $_SESSION['repartidor_nombre'] = $repartidor['nombre'];
                header("Location: ../repartidor/dashboard.php");
                exit();
            } else {
                // Si no está aprobado, enviar error
                header("Location: ../login_repartidor.php?error=Tu cuenta aún no ha sido aprobada.");
                exit();
            }
        }
    }
    // Si los datos son incorrectos
    header("Location: ../login_repartidor.php?error=Credenciales incorrectas.");
    exit();
}
?>