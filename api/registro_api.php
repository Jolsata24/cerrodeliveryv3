<?php
// registro_api.php - Versión para Flutter
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once '../includes/conexion.php';

// Leemos el JSON que envía Flutter
$input = json_decode(file_get_contents("php://input"), true);

// Si no es JSON, intentamos leer POST normal
$nombre   = $input['nombre'] ?? $_POST['nombre'] ?? '';
$email    = $input['email'] ?? $_POST['email'] ?? '';
$password = $input['password'] ?? $_POST['password'] ?? '';
$telefono = $input['telefono'] ?? $_POST['telefono'] ?? '';

if (empty($nombre) || empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Faltan datos obligatorios"]);
    exit();
}

// Verificar si el correo ya existe
$check = $conn->prepare("SELECT id FROM usuarios_clientes WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "El correo ya está registrado"]);
    exit();
}

// Encriptar y Token
$password_hash = password_hash($password, PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(32));

$sql = "INSERT INTO usuarios_clientes (nombre, email, password, telefono, token_verificacion, cuenta_confirmada) VALUES (?, ?, ?, ?, ?, 0)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $nombre, $email, $password_hash, $telefono, $token);

if ($stmt->execute()) {
    // ÉXITO
    echo json_encode(["success" => true, "message" => "Cuenta creada correctamente"]);
} else {
    echo json_encode(["success" => false, "message" => "Error al registrar en BD"]);
}
?>