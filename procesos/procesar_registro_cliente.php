<?php
require_once '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Limpieza de datos
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $telefono = trim($_POST['telefono']);

    // Validar campos vacíos
    if (empty($nombre) || empty($email) || empty($password) || empty($telefono)) {
        header("Location: ../registro_cliente.php?error=campos_vacios");
        exit();
    }

    // 2. Encriptar contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // 3. Generar Token Único para validación
    $token = bin2hex(random_bytes(32)); 

    // 4. Preparar la inserción
    $sql = "INSERT INTO usuarios_clientes (nombre, email, password, telefono, token_verificacion, cuenta_confirmada) VALUES (?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Error preparando la consulta: " . $conn->error);
    }

    $stmt->bind_param("sssss", $nombre, $email, $password_hash, $telefono, $token);
    
    // 5. Ejecutar la consulta con manejo de Excepciones (ESTA ES LA FORMA CORRECTA)
    try {
        $stmt->execute();
        
        // --- SI LLEGA AQUÍ, EL REGISTRO FUE EXITOSO ---
        
        // Detectamos dominio real para el link
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        
        // IMPORTANTE: Verifica si tu proyecto está en una subcarpeta o en la raíz.
        // Si tu web es directamente "cerrodelivery.com", usa la línea de abajo SIN carpeta.
        // Si es "cerrodelivery.com/cerrodeliveryv2", déjalo como está.
        
        // OPCIÓN 1 (Si está en carpeta):
        // $link_activacion = $protocol . "://" . $host . "/cerrodeliveryv2/activar_cuenta.php?token=" . $token;

        // OPCIÓN 2 (Si está en la raíz, que es lo más común al desplegar):
        $link_activacion = $protocol . "://" . $host . "/activar_cuenta.php?token=" . $token;
        
        $mensaje = "Cuenta creada. Por favor verifica tu correo.";
        header("Location: ../login_cliente.php?msg=" . urlencode($mensaje));
        exit();

    } catch (mysqli_sql_exception $e) {
        // --- AQUÍ CAPTURAMOS EL ERROR SI FALLA ---
        
        // El código de error 1062 es "Duplicate entry" (Correo repetido)
        if ($e->getCode() == 1062) {
            header("Location: ../registro_cliente.php?error=existe");
            exit();
        } else {
            // Cualquier otro error de base de datos
            die("Error crítico en la BD: " . $e->getMessage());
        }
    } catch (Exception $e) {
        // Otros errores generales
        die("Error del sistema: " . $e->getMessage());
    }
    
    $stmt->close();
    $conn->close();
}
?>