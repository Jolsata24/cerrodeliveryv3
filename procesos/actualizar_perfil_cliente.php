<?php
session_start();

// CORRECCIÓN: Verificar 'cliente_id'
if (!isset($_SESSION['cliente_id'])) {
    header("Location: ../login_cliente.php");
    exit();
}

require_once '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // CORRECCIÓN: Obtener 'cliente_id'
    $id_cliente = $_SESSION['cliente_id'];
    
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $pass_nueva = $_POST['password_nueva'];
    $pass_confirmar = $_POST['password_confirmar'];

    if (empty($nombre) || empty($telefono)) {
        header("Location: ../mi_perfil.php?error=El nombre y teléfono son obligatorios");
        exit();
    }

    if (!empty($pass_nueva)) {
        if ($pass_nueva !== $pass_confirmar) {
            header("Location: ../mi_perfil.php?error=Las contraseñas no coinciden");
            exit();
        }
        $password_hash = password_hash($pass_nueva, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios_clientes SET nombre = ?, telefono = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $nombre, $telefono, $password_hash, $id_cliente);
    } else {
        $sql = "UPDATE usuarios_clientes SET nombre = ?, telefono = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nombre, $telefono, $id_cliente);
    }

    if ($stmt->execute()) {
        // CORRECCIÓN: Actualizar 'cliente_nombre' para que el Header cambie al instante
        $_SESSION['cliente_nombre'] = $nombre;
        
        header("Location: ../mi_perfil.php?msg=Datos actualizados correctamente");
    } else {
        header("Location: ../mi_perfil.php?error=Error al actualizar: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: ../index.php");
}
?>