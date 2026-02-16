<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad
if (!isset($_SESSION['restaurante_id'])) {
    header('Location: ../login_restaurante.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_plato = $_POST['id_plato'];
    $nombre_plato = trim($_POST['nombre_plato']);
    $precio = trim($_POST['precio']);
    $descripcion = trim($_POST['descripcion']);
    $id_restaurante = $_SESSION['restaurante_id'];

    // 1. Obtener la foto actual para saber si debemos mantenerla o borrarla
    $sql_foto = "SELECT foto_url FROM menu_platos WHERE id = ? AND id_restaurante = ?";
    $stmt_foto = $conn->prepare($sql_foto);
    $stmt_foto->bind_param("ii", $id_plato, $id_restaurante);
    $stmt_foto->execute();
    $res_foto = $stmt_foto->get_result();
    
    if ($res_foto->num_rows == 0) { die("Plato no encontrado."); }
    $plato_actual = $res_foto->fetch_assoc();
    $nombre_imagen = $plato_actual['foto_url']; // Por defecto, mantenemos la misma

    // 2. Verificar si se subió una NUEVA foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $directorio_destino = "../assets/img/platos/";
        
        // Crear carpeta si no existe (importante)
        if (!is_dir($directorio_destino)) {
            mkdir($directorio_destino, 0755, true);
        }

        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nombre_unico = uniqid('plato_') . '.' . $extension;
        $ruta_completa = $directorio_destino . $nombre_unico;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_completa)) {
            // Si la subida fue exitosa:
            // a) Asignamos el nuevo nombre para la base de datos
            $nombre_imagen = $nombre_unico;
            
            // b) Borramos la imagen antigua si no es la "default.jpg"
            $ruta_antigua = $directorio_destino . $plato_actual['foto_url'];
            if ($plato_actual['foto_url'] != 'default.jpg' && file_exists($ruta_antigua)) {
                unlink($ruta_antigua);
            }
        }
    }

    // 3. Actualizar la base de datos
    $sql_update = "UPDATE menu_platos SET nombre_plato=?, descripcion=?, precio=?, foto_url=? WHERE id=? AND id_restaurante=?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("ssdssi", $nombre_plato, $descripcion, $precio, $nombre_imagen, $id_plato, $id_restaurante);

    if ($stmt->execute()) {
        header("Location: ../restaurante/dashboard.php?status=plato_actualizado");
    } else {
        die("Error al actualizar: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
}
?>