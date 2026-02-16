<?php
// PERMITIR CONEXIÓN DESDE CUALQUIER ORIGEN (Ngrok, App, Web)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once '../includes/conexion.php';

// Verificamos qué quiere hacer la App
$accion = $_GET['accion'] ?? '';

switch ($accion) {
    
    case 'listar':
        // Código para obtener TODOS los restaurantes
        $sql = "SELECT * FROM restaurantes WHERE estado = 'activo'";
        $result = $conn->query($sql);
        $datos = [];
        while($row = $result->fetch_assoc()) { $datos[] = $row; }
        echo json_encode($datos);
        break;

    case 'menu':
        // Código para obtener el MENÚ de un restaurante
        $id = $_GET['id'] ?? 0;
        $sql = "SELECT * FROM menu_platos WHERE id_restaurante = ? AND esta_visible = 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $datos = [];
        while($row = $result->fetch_assoc()) { $datos[] = $row; }
        echo json_encode($datos);
        break;

    case 'detalle':
        // Código para obtener info de UN SOLO restaurante
        $id = $_GET['id'] ?? 0;
        $sql = "SELECT * FROM restaurantes WHERE id = ?";
        // ... lógica ...
        break;

    default:
        echo json_encode(["error" => "Acción no válida o no especificada"]);
        break;
}
?>