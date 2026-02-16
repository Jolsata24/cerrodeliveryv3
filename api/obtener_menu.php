<?php
// PERMITIR CONEXIÓN DESDE CUALQUIER ORIGEN (Ngrok, App, Web)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../includes/conexion.php';

$id_restaurante = $_GET['id_restaurante'] ?? '';

if (empty($id_restaurante)) {
    echo json_encode([]);
    exit();
}

// CORRECCIÓN: Tu tabla se llama 'menu_platos', no 'platos'
// Y filtramos por 'esta_visible = 1' igual que en tu web
$sql = "SELECT * FROM menu_platos WHERE id_restaurante = ? AND esta_visible = 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_restaurante);
$stmt->execute();
$result = $stmt->get_result();

$platos = array();

while($row = $result->fetch_assoc()) {
    $platos[] = $row;
}

echo json_encode($platos);
?>