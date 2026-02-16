<?php
session_start();
require_once '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Buscar al cliente por email
    $sql = "SELECT id, nombre, password FROM usuarios_clientes WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $cliente = $result->fetch_assoc();
        
        // Verificar la contraseña encriptada
        if (password_verify($password, $cliente['password'])) {
            // Login exitoso: guardar datos en la sesión
            $_SESSION['cliente_id'] = $cliente['id'];
            $_SESSION['cliente_nombre'] = $cliente['nombre'];
            
            // Redirigir a la página principal
            header("Location: ../index.php");
            exit();
        }
    }

    // Si el email no existe o la contraseña es incorrecta
    header("Location: ../login_cliente.php?error=1");
    exit();
}
?>