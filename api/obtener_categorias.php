<?php
// Permitir acceso desde la App
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Conexión a la BD
require_once '../includes/conexion.php';

try {
    // Consulta simple: Traer todas las categorías
    $sql = "SELECT id, nombre_categoria, icono_categoria FROM categorias ORDER BY nombre_categoria ASC";
    $result = $conn->query($sql);

    $categorias = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Convertimos el ID a string por si acaso Flutter lo espera así, aunque int también sirve
            $row['id'] = (string)$row['id'];
            $categorias[] = $row;
        }
    }

    // Devolver JSON limpio
    echo json_encode($categorias);

} catch (Exception $e) {
    // En caso de error, devolver array vacío para que la app no falle
    echo json_encode([]);
}

$conn->close();
?>