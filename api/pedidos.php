<?php
ob_start(); // 1. INICIAMOS LA LIMPIEZA

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../includes/conexion.php';

$accion = $_GET['accion'] ?? '';

switch ($accion) {

    case 'crear':
        $id_cliente     = $_POST['id_cliente'] ?? null;
        $id_restaurante = $_POST['restaurante_id'] ?? null;
        $direccion      = $_POST['direccion'] ?? '';
        $referencia     = $_POST['referencia'] ?? '';
        $telefono       = $_POST['telefono'] ?? '';
        $metodo_pago    = $_POST['metodo_pago'] ?? 'efectivo';
        $total          = $_POST['total'] ?? 0;
        $latitud        = $_POST['latitud'] ?? 0;
        $longitud       = $_POST['longitud'] ?? 0;

        $carrito_json   = $_POST['carrito'] ?? '[]';
        $carrito        = json_decode($carrito_json, true);

        if (!$id_cliente || !$id_restaurante || empty($carrito)) {
            ob_end_clean(); // BORRAMOS BASURA
            echo json_encode(["success" => false, "message" => "Datos incompletos"]);
            exit;
        }

        $nombre_imagen = null;
        if (isset($_FILES['evidencia_yape']) && $_FILES['evidencia_yape']['error'] === UPLOAD_ERR_OK) {
            $nombre_imagen = "yape_" . time() . "_" . $id_cliente . ".jpg";
            $ruta_destino = "../assets/img/comprobantes/" . $nombre_imagen;
            move_uploaded_file($_FILES['evidencia_yape']['tmp_name'], $ruta_destino);
        }

        $conn->begin_transaction();

        try {
            $sql = "INSERT INTO pedidos (id_cliente, id_restaurante, direccion_pedido, referencia, telefono_pedido, metodo_pago, monto_total, latitud, longitud, foto_yape, fecha_pedido, estado_pedido) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Pendiente')";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissssddss", $id_cliente, $id_restaurante, $direccion, $referencia, $telefono, $metodo_pago, $total, $latitud, $longitud, $nombre_imagen);
            $stmt->execute();
            $id_pedido = $conn->insert_id;

            $sql_detalle = "INSERT INTO detalle_pedidos (id_pedido, id_plato, cantidad, precio_unitario, nombre_plato) VALUES (?, ?, ?, ?, ?)";
            $stmt_detalle = $conn->prepare($sql_detalle);

            foreach ($carrito as $item) {
                $stmt_detalle->bind_param("iiids", $id_pedido, $item['id'], $item['cantidad'], $item['precio'], $item['nombre']);
                $stmt_detalle->execute();
            }

            $conn->commit();
            ob_end_clean(); // BORRAMOS BASURA
            echo json_encode(["success" => true, "pedido_id" => $id_pedido, "message" => "Pedido creado"]);
        } catch (Exception $e) {
            $conn->rollback();
            ob_end_clean(); // BORRAMOS BASURA
            echo json_encode(["success" => false, "message" => "Error BD: " . $e->getMessage()]);
        }
        break;

    case 'listar':
        $id_cliente = $_GET['id_cliente'] ?? 0;

        $sql = "SELECT p.id, p.fecha_pedido, p.monto_total, p.estado_pedido, p.id_restaurante as restaurante_id, 
                       COALESCE(r.nombre_restaurante, 'Restaurante no disponible') as nombre_restaurante, 
                       COALESCE(r.imagen_fondo, 'default.png') as imagen_fondo,
                       COALESCE(r.telefono, '') as telefono_restaurante
                FROM pedidos p
                LEFT JOIN restaurantes r ON p.id_restaurante = r.id
                WHERE p.id_cliente = ?
                ORDER BY p.fecha_pedido DESC";

        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
             ob_end_clean();
             echo json_encode(["error" => "Error SQL"]);
             exit;
        }

        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $res = $stmt->get_result();

        $pedidos = [];
        while ($row = $res->fetch_assoc()) {
            $pedidos[] = $row;
        }
        
        ob_end_clean(); // 2. BORRAMOS CUALQUIER BASURA ANTES DE ENVIAR
        echo json_encode($pedidos); // 3. ENVIAMOS EL JSON LIMPIO
        break;

    default:
        ob_end_clean();
        echo json_encode(["success" => false, "message" => "Accion no valida"]);
}
?>