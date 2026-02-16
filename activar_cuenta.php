<?php
require_once 'includes/conexion.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // 1. Buscar usuario con ese token
    $sql = "SELECT id FROM usuarios_clientes WHERE token_verificacion = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // 2. Activar cuenta y borrar token para que no se use de nuevo
        $update = "UPDATE usuarios_clientes SET cuenta_confirmada = 1, token_verificacion = NULL WHERE token_verificacion = ?";
        $stmt_up = $conn->prepare($update);
        $stmt_up->bind_param("s", $token);
        
        if ($stmt_up->execute()) {
            header("Location: login_cliente.php?msg=Cuenta activada correctamente. Ya puedes ingresar.");
        } else {
            echo "Error al activar la cuenta.";
        }
    } else {
        echo "<h1>Token inválido o cuenta ya activada.</h1><br><a href='login_cliente.php'>Ir al Login</a>";
    }
} else {
    header("Location: index.php");
}
?>