<?php
// PERMITIR CONEXIÓN DESDE CUALQUIER ORIGEN (Ngrok, App, Web)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
header('Content-Type: application/json'); // Importante: Decirle a la app que esto es JSON
require_once '../includes/conexion.php'; // Usamos tu conexión existente

// Recibir los datos que envía Flutter (JSON)
$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Faltan datos"]);
    exit();
}

// Tu lógica SQL original
$sql = "SELECT id, nombre, password FROM usuarios_clientes WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $cliente = $result->fetch_assoc();
    if (password_verify($password, $cliente['password'])) {
        // ÉXITO: Devolvemos true y los datos del usuario
        echo json_encode([
            "success" => true,
            "message" => "Login exitoso",
            "user_data" => [
                "id" => $cliente['id'],
                "nombre" => $cliente['nombre']
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Contraseña incorrecta"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Email no encontrado"]);
}
?>