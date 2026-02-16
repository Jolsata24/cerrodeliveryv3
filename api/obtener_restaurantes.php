<?php
// Permitir acceso desde la App
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../includes/conexion.php';

// 1. Recibir parámetros de la App
$busqueda = isset($_GET['q']) ? $_GET['q'] : '';
$categoria_id = isset($_GET['cat']) ? $_GET['cat'] : '';

// 2. Construcción de la Consulta SQL Dinámica
// Seleccionamos los datos del restaurante
$sql = "SELECT r.id, 
               r.nombre_restaurante, 
               r.imagen_fondo, 
               r.puntuacion_promedio, 
               r.direccion, 
               r.estado,
               r.telefono,
               -- Tiempo estimado estático (puedes hacerlo dinámico luego)
               '20-30 min' as tiempo_entrega
        FROM restaurantes r ";

// --- LOGICA DE FILTRADO POR CATEGORÍA ---
// Si hay una categoría seleccionada, hacemos JOIN con la tabla intermedia
if (!empty($categoria_id)) {
    $sql .= " INNER JOIN restaurante_categorias rc ON r.id = rc.id_restaurante ";
}

// Condición Base: Solo restaurantes activos
$sql .= " WHERE r.estado = 'activo' ";

// Filtro por Categoría
if (!empty($categoria_id)) {
    // Aseguramos que sea un entero para evitar inyección SQL básica
    $cat_id_seguro = intval($categoria_id);
    $sql .= " AND rc.id_categoria = $cat_id_seguro ";
}

// Filtro por Búsqueda (Texto)
if (!empty($busqueda)) {
    $busqueda_segura = $conn->real_escape_string($busqueda);
    $sql .= " AND r.nombre_restaurante LIKE '%$busqueda_segura%' ";
}

// Ordenar por mejores puntuados primero
$sql .= " ORDER BY r.puntuacion_promedio DESC";

// 3. Ejecutar y Devolver
try {
    $result = $conn->query($sql);
    $restaurantes = [];

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Asegurar tipos de datos correctos para Flutter
            $row['id'] = (int)$row['id'];
            $row['puntuacion_promedio'] = (double)$row['puntuacion_promedio'];
            
            // Si la imagen es nula, poner la default
            if (empty($row['imagen_fondo'])) {
                $row['imagen_fondo'] = 'default_restaurante.jpg';
            }
            
            $restaurantes[] = $row;
        }
    }

    echo json_encode($restaurantes);

} catch (Exception $e) {
    echo json_encode([]);
}

$conn->close();
?>