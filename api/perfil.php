<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once '../includes/conexion.php';

$accion = $_GET['accion'] ?? '';
$id_cliente = $_GET['id_cliente'] ?? $_POST['id_cliente'] ?? null;

if (!$id_cliente) {
    echo json_encode(["success" => false, "message" => "Falta ID cliente"]);
    exit();
}

switch ($accion) {
    // 1. OBTENER DATOS ACTUALES
    case 'obtener':
        $sql = "SELECT nombre, email, telefono FROM usuarios_clientes WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($row = $res->fetch_assoc()) {
            echo json_encode(["success" => true, "data" => $row]);
        } else {
            echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
        }
        break;

    // 2. ACTUALIZAR DATOS
    case 'actualizar':
        $nombre = $_POST['nombre'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $password = $_POST['password'] ?? ''; // Opcional

        if (empty($nombre) || empty($telefono)) {
            echo json_encode(["success" => false, "message" => "Nombre y teléfono obligatorios"]);
            exit();
        }

        if (!empty($password)) {
            // SI CAMBIA CONTRASEÑA
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios_clientes SET nombre = ?, telefono = ?, password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $nombre, $telefono, $pass_hash, $id_cliente);
        } else {
            // SI SOLO CAMBIA DATOS (Mantiene pass vieja)
            $sql = "UPDATE usuarios_clientes SET nombre = ?, telefono = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $nombre, $telefono, $id_cliente);
        }

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Perfil actualizado"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al actualizar"]);
        }
        break;

    default:
        echo json_encode(["success" => false, "message" => "Acción inválida"]);
}
?>