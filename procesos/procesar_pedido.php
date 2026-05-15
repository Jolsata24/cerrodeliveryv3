<?php
// === ACTIVAR REPORTE DE ERRORES (Para depuración) ===
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../includes/conexion.php';

// =========================================================================
// ⏰ NUEVO: VALIDACIÓN DE HORARIO DE ATENCIÓN
// =========================================================================
date_default_timezone_set('America/Lima'); // Asegurar hora exacta de Perú
$hora_actual = date('H:i');

// Obtener horarios de la BD
$sql_horario = "SELECT nombre_config, valor_config FROM configuracion WHERE nombre_config IN ('horario_apertura', 'horario_cierre')";
$res_horario = $conn->query($sql_horario);
$apertura = '18:30'; $cierre = '23:00'; // Valores por defecto en caso de fallo

if ($res_horario && $res_horario->num_rows > 0) {
    while($row = $res_horario->fetch_assoc()) {
        if($row['nombre_config'] == 'horario_apertura') $apertura = $row['valor_config'];
        if($row['nombre_config'] == 'horario_cierre') $cierre = $row['valor_config'];
    }
}

$fuera_de_horario = false;
// Lógica que soporta incluso si decides cerrar de madrugada (Ej: 18:00 a 02:00)
if ($apertura < $cierre) {
    if ($hora_actual < $apertura || $hora_actual > $cierre) $fuera_de_horario = true;
} else {
    if ($hora_actual < $apertura && $hora_actual > $cierre) $fuera_de_horario = true;
}

if ($fuera_de_horario) {
    header("Location: ../index.php?error=horario_cerrado");
    exit();
}
// =========================================================================


// Verificar sesión
if (!isset($_SESSION['cliente_id'])) {
    header("Location: ../login_cliente.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Recibir Datos del Formulario
    $cliente_id = $_SESSION['cliente_id'];
    $restaurante_id = $_POST['restaurante_id'] ?? null;
    // =========================================================================
// 🔒 NUEVO: VALIDACIÓN DE SEGURIDAD - ¿EL RESTAURANTE ESTÁ ABIERTO?
// =========================================================================
$sql_seguridad = "SELECT estado FROM restaurantes WHERE id = ?";
$stmt_seguridad = $conn->prepare($sql_seguridad);
$stmt_seguridad->bind_param("i", $restaurante_id);
$stmt_seguridad->execute();
$resultado_seguridad = $stmt_seguridad->get_result();

if ($resultado_seguridad->num_rows > 0) {
    $restaurante_check = $resultado_seguridad->fetch_assoc();
    
    // Si la columna estado dice "Cerrado" (o si usas 0 para cerrado, cámbialo aquí)
    if (strtolower($restaurante_check['estado']) === 'cerrado' || $restaurante_check['estado'] == 0) {
        
        // El usuario intentó saltarse las reglas. Lo botamos con un error.
        header("Location: ../index.php?error=restaurante_cerrado");
        exit();
    }
} else {
    // Si inventó un ID de restaurante que ni siquiera existe
    die("Error de Seguridad: El restaurante no es válido.");
}
$stmt_seguridad->close();
    $direccion = $_POST['direccion'] ?? '';
    $referencia = $_POST['referencia'] ?? '-';
    
    // Coordenadas
    $latitud = $_POST['latitud'] ?? null;
    $longitud = $_POST['longitud'] ?? null;
    
    $telefono = $_POST['telefono'] ?? '';
    $metodo_pago = $_POST['metodo_pago'] ?? '';
    
    // --- MONTOS (LO NUEVO) ---
    $total = $_POST['total'] ?? 0;
    $costo_envio = $_POST['costo_envio'] ?? 0; // <--- AQUÍ RECIBIMOS EL ENVÍO
    // -------------------------

    $carrito_json = $_POST['carrito'] ?? '[]';

    // Validaciones básicas
    if (empty($restaurante_id) || empty($direccion) || empty($metodo_pago)) {
        die("❌ ERROR: Faltan datos obligatorios (Restaurante, Dirección o Pago).");
    }

    $carrito = json_decode($carrito_json, true);
    if (json_last_error() !== JSON_ERROR_NONE || empty($carrito)) {
        die("❌ ERROR: El carrito está vacío o tiene un formato inválido.");
    }

    // 2. Subir Foto Yape (si aplica)
    $foto_yape = null;
    if ($metodo_pago == 'yape') {
        if (isset($_FILES['evidencia_yape']) && $_FILES['evidencia_yape']['error'] == 0) {
            $directorio = "../assets/img/comprobantes/";
            if (!file_exists($directorio)) mkdir($directorio, 0777, true);
            
            $extension = pathinfo($_FILES['evidencia_yape']['name'], PATHINFO_EXTENSION);
            $nombre_archivo = "pago_" . time() . "_" . $cliente_id . "." . $extension;
            
            if (move_uploaded_file($_FILES['evidencia_yape']['tmp_name'], $directorio . $nombre_archivo)) {
                $foto_yape = $nombre_archivo;
            } else {
                die("❌ ERROR: No se pudo guardar la imagen en el servidor.");
            }
        } else {
            // Manejo de errores de subida
            $error_code = $_FILES['evidencia_yape']['error'] ?? 'No enviado';
            if ($error_code != 4) { // 4 = No file uploaded (si no es obligatorio estricto)
                 // Si quieres que sea obligatorio, descomenta el die:
                 // die("❌ ERROR: Debes subir la captura del Yape.");
            }
        }
    }

    // 3. Insertar Pedido en Base de Datos
    // OJO: Asegúrate de haber ejecutado el ALTER TABLE para agregar 'costo_envio'
    
    $sql = "INSERT INTO pedidos (
                id_cliente, id_restaurante, id_repartidor, 
                direccion_pedido, referencia, latitud, longitud, 
                telefono_pedido, metodo_pago, foto_yape, 
                monto_total, costo_envio, estado_pedido, fecha_pedido
            ) 
            VALUES (?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', NOW())";
            
    $stmt = $conn->prepare($sql);
    
    // Tipos de datos para bind_param:
    // i = integer, s = string, d = double (decimal)
    // Orden: 
    // id_cliente (i), id_restaurante (i), direccion (s), referencia (s), lat (s), lon (s), 
    // tel (s), pago (s), foto (s), total (d), costo_envio (d)
    // Total: ii s s s s s s s d d
    
    $stmt->bind_param("iisssssssdd", 
        $cliente_id, 
        $restaurante_id, 
        $direccion, 
        $referencia, 
        $latitud, 
        $longitud, 
        $telefono, 
        $metodo_pago, 
        $foto_yape, 
        $total,       // El monto total pagado por el cliente
        $costo_envio  // El costo del delivery (separado)
    );
    
    if ($stmt->execute()) {
        $pedido_id = $stmt->insert_id;
        
        // 4. Insertar Detalles del Pedido (Platos)
        $sql_det = "INSERT INTO detalle_pedidos (id_pedido, id_plato, cantidad, precio_unitario, nombre_plato) VALUES (?, ?, ?, ?, ?)";
        $stmt_det = $conn->prepare($sql_det);
        
        foreach ($carrito as $item) {
            $nombre_plato = $item['nombre'] ?? 'Plato sin nombre';
            $stmt_det->bind_param("iiids", $pedido_id, $item['id'], $item['cantidad'], $item['precio'], $nombre_plato);
            $stmt_det->execute();
        }
        
        // Redirigir al cliente
        header("Location: ../mis_pedidos.php?msg=pedido_exitoso");
        exit();

    } else {
        die("❌ ERROR SQL AL GUARDAR: " . $stmt->error);
    }

} else {
    // Si intentan entrar directo sin POST
    header("Location: ../index.php");
    exit();
}
?>