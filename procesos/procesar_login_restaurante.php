<?php
// Es crucial iniciar la sesión ANTES de cualquier salida HTML
session_start(); 

require_once '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        // Redirigir si los campos están vacíos
        header("Location: ../login_restaurante.php?error=1");
        exit();
    }

    // 1. Buscar el usuario por su email
    $sql = "SELECT id, nombre_restaurante, password FROM restaurantes WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // 2. Verificar si el usuario existe (si se encontró una fila)
    if ($result->num_rows == 1) {
        $restaurante = $result->fetch_assoc();
        
        // 3. Verificar si la contraseña coincide con la encriptada en la BD
        if (password_verify($password, $restaurante['password'])) {
            // ¡Login exitoso!
            
            // 4. Guardar datos en la sesión para usarlos después
            $_SESSION['restaurante_id'] = $restaurante['id'];
            $_SESSION['restaurante_nombre'] = $restaurante['nombre_restaurante'];
            
            // Redirigir al panel del restaurante (que crearemos a continuación)
            header("Location: ../restaurante/dashboard.php");
            exit();
        }
    }

    // Si el usuario no existe o la contraseña es incorrecta, redirigir con error
    header("Location: ../login_restaurante.php?error=1");
    exit();

} else {
    // Si alguien intenta acceder al archivo directamente, lo sacamos
    header("Location: ../login_restaurante.php");
    exit();
}
?>