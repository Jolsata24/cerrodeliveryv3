<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad: solo restaurantes logueados
if (!isset($_SESSION['restaurante_id'])) {
    die("Acceso denegado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['foto_restaurante'])) {

    $id_restaurante = $_SESSION['restaurante_id'];
    $foto = $_FILES['foto_restaurante'];

    // Validar que no haya errores en la subida
    if ($foto['error'] === UPLOAD_ERR_OK) {
        $nombre_temporal = $foto['tmp_name'];
        
        // Crear un nombre de archivo único para evitar conflictos
        $extension = pathinfo($foto['name'], PATHINFO_EXTENSION);
        $nombre_unico = "restaurante_" . $id_restaurante . "_" . time() . "." . $extension;

        // Definir la carpeta de destino
        $directorio_destino = "../assets/img/restaurantes/";
        if (!is_dir($directorio_destino)) {
            mkdir($directorio_destino, 0755, true);
        }
        $ruta_final = $directorio_destino . $nombre_unico;

        // Mover el archivo a la carpeta de destino
        if (move_uploaded_file($nombre_temporal, $ruta_final)) {
            
            // Actualizar la base de datos con el nuevo nombre de archivo
            $sql = "UPDATE restaurantes SET imagen_fondo = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $nombre_unico, $id_restaurante);
            
            if ($stmt->execute()) {
                header("Location: ../restaurante/dashboard.php?status=imagen_actualizada");
            } else {
                die("Error al actualizar la base de datos.");
            }
            $stmt->close();
        } else {
            die("Error al mover el archivo de imagen.");
        }
    } else {
        die("Error en la subida del archivo.");
    }
    $conn->close();
} else {
    header("Location: ../restaurante/dashboard.php");
    exit();
}
?>