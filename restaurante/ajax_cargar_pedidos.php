<?php
session_start();
require_once '../includes/conexion.php';

// Verificar sesión
if (!isset($_SESSION['restaurante_id'])) {
    die();
}
$id_restaurante = $_SESSION['restaurante_id'];
$nombre_restaurante = isset($_SESSION['restaurante_nombre']) ? $_SESSION['restaurante_nombre'] : 'Restaurante';

// 1. CONSULTA SQL (MODIFICADA: Agregamos p.latitud y p.longitud)
$sql_pedidos = "SELECT p.id, p.fecha_pedido, p.monto_total, p.costo_envio, p.estado_pedido, 
                       p.direccion_pedido, p.latitud, p.longitud, p.metodo_pago, p.foto_yape, 
                       c.nombre as nombre_cliente, c.telefono as telefono_cliente
                FROM pedidos p
                JOIN usuarios_clientes c ON p.id_cliente = c.id
                WHERE p.id_restaurante = ?
                ORDER BY p.fecha_pedido DESC";
                
$stmt_pedidos = $conn->prepare($sql_pedidos);
$stmt_pedidos->bind_param("i", $id_restaurante);
$stmt_pedidos->execute();
$resultado_pedidos = $stmt_pedidos->get_result();

if ($resultado_pedidos->num_rows > 0):
    while ($pedido = $resultado_pedidos->fetch_assoc()):
        // Lógica de colores según estado
        $estado_clase_borde = 'border-info';
        $estado_clase_texto = 'text-info';
        $icono_estado = 'bi-stopwatch';

        switch ($pedido['estado_pedido']) {
            case 'Pendiente':
                $estado_clase_borde = 'border-danger';
                $estado_clase_texto = 'text-danger';
                $icono_estado = 'bi-exclamation-circle-fill';
                break;
            case 'En preparación':
                $estado_clase_borde = 'border-warning';
                $estado_clase_texto = 'text-warning';
                $icono_estado = 'bi-egg-fried';
                break;
            case 'Listo para recoger':
                $estado_clase_borde = 'border-primary';
                $estado_clase_texto = 'text-primary';
                $icono_estado = 'bi-bag-check-fill';
                break;
            case 'En camino':
                $estado_clase_borde = 'border-success';
                $estado_clase_texto = 'text-success';
                $icono_estado = 'bi-truck';
                break;
            case 'Entregado':
                $estado_clase_borde = 'border-secondary';
                $estado_clase_texto = 'text-secondary';
                $icono_estado = 'bi-check2-circle';
                break;
        }
?>
        <div class="card pedido-card shadow-sm mb-4 <?php echo $estado_clase_borde; ?>">
            <div class="card-body">
                <div class="row align-items-center">
                    
                    <div class="col-md-4">
                        <h5 class="fw-bold mb-1">Pedido #<?php echo $pedido['id']; ?></h5>
                        <p class="mb-1"><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['nombre_cliente']); ?></p>
                        <p class="text-muted mb-0"><small><?php echo date('d/m/Y h:i A', strtotime($pedido['fecha_pedido'])); ?></small></p>
                    </div>

                    <div class="col-md-4 text-center my-3 my-md-0">
                        <h6 class="text-uppercase small">Estado</h6>
                        <div class="d-flex align-items-center justify-content-center <?php echo $estado_clase_texto; ?>">
                            <i class="bi <?php echo $icono_estado; ?> fs-4 me-2"></i>
                            <span class="fw-bold fs-5"><?php echo htmlspecialchars($pedido['estado_pedido']); ?></span>
                        </div>
                    </div>

                    <div class="col-md-4 text-md-end">
                        <h6 class="text-uppercase small text-muted mb-2">Desglose</h6>

                        <div class="mb-2">
                            <span class="d-block small text-muted">Total Cobrado:</span>
                            <span class="fw-bold fs-5 text-dark">S/ <?php echo number_format($pedido['monto_total'], 2); ?></span>
                        </div>

                        <div class="p-2 bg-light border rounded mb-2">
                            <span class="d-block small text-danger fw-bold">Pagar al Motorizado:</span>
                            <span class="fs-6 text-danger">
                                - S/ <?php echo number_format($pedido['costo_envio'], 2); ?>
                            </span>
                        </div>
                        
                        <div class="mb-3">
                            <span class="d-block small text-success fw-bold">Tu Ganancia Neta:</span>
                            <span class="fw-bold fs-5 text-success">
                                S/ <?php echo number_format($pedido['monto_total'] - $pedido['costo_envio'], 2); ?>
                            </span>
                        </div>

                        <div class="mt-2 border-top pt-2">
                            <?php if ($pedido['metodo_pago'] == 'yape' && !empty($pedido['foto_yape'])): ?>
                                
                                <div class="d-flex justify-content-md-end align-items-center gap-2">
                                    <span class="small text-muted me-1 fw-bold text-primary">Ver Yape:</span>
                                    <div class="position-relative" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modalZoom<?php echo $pedido['id']; ?>">
                                        <img src="../assets/img/comprobantes/<?php echo htmlspecialchars($pedido['foto_yape']); ?>" 
                                             alt="Comprobante" 
                                             class="rounded border border-2 border-success shadow-sm"
                                             style="width: 60px; height: 60px; object-fit: cover; transition: transform 0.2s;"
                                             onmouseover="this.style.transform='scale(1.1)'" 
                                             onmouseout="this.style.transform='scale(1)'">
                                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle">
                                            <i class="bi bi-zoom-in text-white" style="font-size: 0.7rem;"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="modal fade" id="modalZoom<?php echo $pedido['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg"> 
                                        <div class="modal-content border-0 bg-transparent"> 
                                            <div class="text-end mb-2">
                                                <button type="button" class="btn-close btn-close-white fs-5" data-bs-dismiss="modal" aria-label="Close" style="opacity: 1; background-color: white; border-radius: 50%; padding: 0.5rem;"></button>
                                            </div>
                                            <div class="modal-body p-0 text-center">
                                                <img src="../assets/img/comprobantes/<?php echo htmlspecialchars($pedido['foto_yape']); ?>" 
                                                     class="img-fluid rounded shadow-lg" 
                                                     style="max-height: 85vh; width: auto; object-fit: contain; background-color: #fff;">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <?php elseif ($pedido['metodo_pago'] == 'efectivo'): ?>
                                <span class="badge bg-secondary p-2"><i class="bi bi-cash-coin me-1"></i>Pago en Efectivo</span>
                            <?php else: ?>
                                <span class="badge bg-primary">Tarjeta/Otro</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-light">
                
                <?php if ($pedido['estado_pedido'] == 'Pendiente'): ?>
                    <div class="d-grid gap-2">
                        <form action="../procesos/actualizar_estado_pedido.php" method="POST">
                            <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                            <input type="hidden" name="nuevo_estado" value="En preparación">
                            <button type="submit" class="btn btn-success w-100 fw-bold">
                                <i class="bi bi-check-circle-fill me-2"></i>CONFIRMAR PEDIDO
                            </button>
                        </form>
                    </div>

                <?php elseif ($pedido['estado_pedido'] == 'En preparación'): ?>
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <button type="button" class="btn btn-info text-white flex-grow-1 fw-bold" data-bs-toggle="modal" data-bs-target="#modalDelivery<?php echo $pedido['id']; ?>">
                            <i class="bi bi-whatsapp me-2"></i>SOLICITAR REPARTIDOR
                        </button>

                        <form action="../procesos/actualizar_estado_pedido.php" method="POST">
                            <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                            <input type="hidden" name="nuevo_estado" value="Listo para recoger">
                            <button type="submit" class="btn btn-outline-primary" title="Marcar como listo para recoger">
                                <i class="bi bi-arrow-right"></i>
                            </button>
                        </form>
                    </div>

                    <div class="modal fade" id="modalDelivery<?php echo $pedido['id']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title">Solicitar Delivery - Pedido #<?php echo $pedido['id']; ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <p class="mb-3">Selecciona una agencia para enviar los datos del pedido por WhatsApp:</p>
                                    
                                    <?php 
                                    // === CONSTRUCCIÓN DEL MENSAJE DE WHATSAPP CON MAPA ===
                                    $mensaje = "Hola, soy el restaurante *{$nombre_restaurante}*.\n";
                                    $mensaje .= "Necesito un repartidor para el *Pedido #{$pedido['id']}*.\n\n";
                                    $mensaje .= " *Dirección Cliente:* " . $pedido['direccion_pedido'] . "\n";
                                    
                                    // -- LÓGICA DEL ENLACE GPS (NUEVO) --
                                    if (!empty($pedido['latitud']) && !empty($pedido['longitud'])) {
                                        // Enlace oficial de Google Maps
                                        $link_maps = "https://www.google.com/maps/search/?api=1&query=" . $pedido['latitud'] . "," . $pedido['longitud'];
                                        $mensaje .= " *Ubicación GPS:* " . $link_maps . "\n";
                                    } else {
                                        $mensaje .= " (Ubicación GPS no disponible)\n";
                                    }
                                    
                                    $mensaje .= " *Cliente:* " . $pedido['nombre_cliente'] . "\n";
                                    $mensaje .= " *Tel:* " . $pedido['telefono_cliente'] . "\n";
                                    $mensaje .= " *Total a Cobrar:* S/ " . number_format($pedido['monto_total'], 2);
                                    
                                    if($pedido['costo_envio'] > 0){
                                        $mensaje .= "\n *Pago Delivery:* S/ " . number_format($pedido['costo_envio'], 2);
                                    }

                                    $mensaje_encoded = urlencode($mensaje);
                                    
                                    // LISTA DE AGENCIAS
                                    $agencias = [
                                        ['nombre' => 'Moto Express', 'celular' => '51969704480'], 
                                        ['nombre' => 'Rapidito Delivery', 'celular' => '51969704480'],
                                        ['nombre' => 'Fast Pasco', 'celular' => '51969704480']
                                    ];
                                    ?>

                                    <div class="d-grid gap-3">
                                        <?php foreach($agencias as $agencia): ?>
                                            <a href="https://wa.me/<?php echo $agencia['celular']; ?>?text=<?php echo $mensaje_encoded; ?>" 
                                               target="_blank" 
                                               class="btn btn-outline-success btn-lg d-flex justify-content-between align-items-center">
                                                <span class="fw-bold"><i class="bi bi-scooter me-2"></i><?php echo $agencia['nombre']; ?></span>
                                                <i class="bi bi-whatsapp fs-4"></i>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-center bg-light">
                                    <small class="text-muted">Al hacer clic, se abrirá el chat con los datos precargados.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($pedido['estado_pedido'] == 'Listo para recoger'): ?>
                    <h6 class="small fw-bold mb-2 text-center text-primary">REPARTIDORES POSTULANDO (APP):</h6>
                    <div class="solicitudes-container" data-id-pedido="<?php echo $pedido['id']; ?>"></div>
                    
                    <div class="text-end mt-2 pt-2 border-top">
                         <form action="../procesos/actualizar_estado_pedido.php" method="POST" class="d-inline">
                            <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                            <input type="hidden" name="nuevo_estado" value="Entregado">
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                Entregado manualmente
                            </button>
                        </form>
                    </div>

                <?php elseif ($pedido['estado_pedido'] == 'En camino'): ?>
                     <div class="alert alert-success py-2 mb-0 text-center">
                        <i class="bi bi-truck me-2"></i>Pedido en ruta de entrega
                     </div>

                <?php else: ?>
                    <p class="text-muted text-center mb-0 small">Este pedido ya fue completado.</p>
                <?php endif; ?>
            </div>
        </div>
<?php 
    endwhile;
else: 
?>
    <div class="text-center p-5">
        <img src="../assets/img/empty-orders.svg" alt="Sin pedidos" style="width: 150px;" class="mb-3">
        <h4 class="fw-bold">No tienes pedidos activos</h4>
        <p class="text-muted">Cuando un cliente realice una compra, aparecerá aquí.</p>
    </div>
<?php endif;

$stmt_pedidos->close();
$conn->close();
?>