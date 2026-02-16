<?php
require_once '../includes/conexion.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recogemos datos
    $nombre = trim($_POST['nombre_restaurante']);
    $telefono = trim($_POST['telefono']); // <--- NUEVO CAMPO
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validamos
    if (empty($nombre) || empty($email) || empty($password) || empty($telefono)) {
        die("Error: Todos los campos son obligatorios.");
    }
    
    // Encriptamos contraseña
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    // Insertamos incluyendo el telefono
    // Asegúrate de que tu tabla 'restaurantes' tenga la columna 'telefono'
    $sql = "INSERT INTO restaurantes (nombre_restaurante, email, password, telefono) VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    // Notar que ahora son 4 parámetros "ssss"
    $stmt->bind_param("ssss", $nombre, $email, $password_hash, $telefono);
    
    if ($stmt->execute()) {
        header("Location: ../login_restaurante.php?status=success");
        exit();
    } else {
        if ($conn->errno == 1062) {
            die("Error: El correo electrónico ya está registrado.");
        } else {
            die("Error al registrar: " . $stmt->error);
        }
    }
    
    $stmt->close();
    $conn->close();
}
?>