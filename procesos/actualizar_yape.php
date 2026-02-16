<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad: solo restaurantes logueados
if (!isset($_SESSION['restaurante_id'])) {
    die("Acceso denegado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_restaurante = $_SESSION['restaurante_id'];
    $numero = $_POST['yape_numero'];
    
    // 1. Actualizar el número
    $sql = "UPDATE restaurantes SET yape_numero = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $numero, $id_restaurante);
    $stmt->execute();
    $stmt->close();

    // 2. Procesar la imagen si se subió una
    if (isset($_FILES['yape_qr']) && $_FILES['yape_qr']['error'] == 0) {
        $directorio = "../assets/img/qr/";
        if (!is_dir($directorio)) { mkdir($directorio, 0755, true); }

        $ext = pathinfo($_FILES['yape_qr']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = "qr_" . $id_restaurante . "." . $ext;
        
        if (move_uploaded_file($_FILES['yape_qr']['tmp_name'], $directorio . $nombre_archivo)) {
            $sql_img = "UPDATE restaurantes SET yape_qr = ? WHERE id = ?";
            $stmt_img = $conn->prepare($sql_img);
            $stmt_img->bind_param("si", $nombre_archivo, $id_restaurante);
            $stmt_img->execute();
        }
    }

    header("Location: ../restaurante/dashboard.php?status=yape_actualizado");
    exit();
}
?>