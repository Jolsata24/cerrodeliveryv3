<?php
session_start();
require_once '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);

    // Buscamos al administrador por su nombre de usuario
    $sql = "SELECT * FROM administradores WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();
        // Verificamos que la contraseña coincida con el hash
        if (password_verify($password, $admin['password'])) {
            // Login correcto
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_usuario'] = $admin['usuario'];
            header("Location: ../admin/dashboard.php");
            exit();
        }
    }
    // Si algo falla, redirigimos de vuelta con un error
    header("Location: ../admin/login.php?error=1");
    exit();
}
?>