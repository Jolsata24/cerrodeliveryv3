<?php
session_start();
// Seguridad: Solo repartidores pueden ver esto
if (!isset($_SESSION['repartidor_id'])) {
    header('Location: ../login_repartidor.php');
    exit();
}
require_once '../includes/conexion.php';
include '../includes/header.php';
?>

<div class="hero-quickbite">
    <div class="container hero-text">
        <div class="dashboard-header d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 fw-bold">Pedidos Disponibles</h1>
                <p class="lead text-white-50 mb-0">Hola, <?php echo htmlspecialchars($_SESSION['repartidor_nombre']); ?>. ¡Nuevas oportunidades te esperan!</p>
            </div>
            
            <div class="d-flex align-items-center mt-3 mt-md-0 gap-2">
                <button type="button" class="btn btn-warning text-dark fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAfiliacion">
                    <i class="bi bi-shop-window me-2"></i>Afiliarse a Restaurantes
                </button>

                <a href="mis_entregas.php" id="btn-mis-entregas" class="btn btn-primary shadow-sm"><i class="bi bi-truck me-2"></i>Ver Mis Entregas</a>
                <a href="../procesos/logout_repartidor.php" class="btn btn-outline-danger shadow-sm"><i class="bi bi-box-arrow-right"></i>Cerrar Sesión</a>
            </div>
        </div>
    </div>
</div>

<div class="main-content-overlay">
    <div class="container">
        <div id="pedidos-disponibles-container">
            <div class="text-center p-5">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Buscando pedidos...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAfiliacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-briefcase-fill me-2"></i>Restaurantes Disponibles para Afiliación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body bg-light">
                <div class="container-fluid">
                    <div class="row g-3">
                        <?php
                        $id_repartidor = $_SESSION['repartidor_id'];
                        
                        // CONSULTA: Traer restaurantes donde NO estoy afiliado (ni pendiente ni aprobado)
                        // Usamos la columna correcta 'imagen_fondo'
                        $sql_restaurantes = "SELECT r.id, r.nombre_restaurante, r.direccion, r.imagen_fondo 
                                             FROM restaurantes r 
                                             WHERE r.id NOT IN (
                                                SELECT id_restaurante 
                                                FROM repartidor_afiliaciones 
                                                WHERE id_repartidor = ?
                                             )";
                                             
                        $stmt_r = $conn->prepare($sql_restaurantes);
                        $stmt_r->bind_param("i", $id_repartidor);
                        $stmt_r->execute();
                        $res_r = $stmt_r->get_result();

                        if ($res_r->num_rows > 0):
                            while ($rest = $res_r->fetch_assoc()):
                                // Lógica de imagen: Si está vacía, usar fondo genérico
                                $img_bg = !empty($rest['imagen_fondo']) ? '../assets/img/' . $rest['imagen_fondo'] : '../assets/img/fondo1.jpg';
                        ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 shadow-sm border-0">
                                    <div style="height: 150px; background-image: url('<?php echo htmlspecialchars($img_bg); ?>'); background-size: cover; background-position: center; border-radius: 5px 5px 0 0;">
                                    </div>
                                    
                                    <div class="card-body">
                                        <h5 class="card-title fw-bold text-primary mb-1"><?php echo htmlspecialchars($rest['nombre_restaurante']); ?></h5>
                                        <p class="card-text small text-muted mb-3">
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i> 
                                            <?php echo !empty($rest['direccion']) ? htmlspecialchars($rest['direccion']) : 'Dirección no registrada'; ?>
                                        </p>
                                        
                                        <form action="../procesos/solicitar_afiliacion.php" method="POST">
                                            <input type="hidden" name="id_restaurante" value="<?php echo $rest['id']; ?>">
                                            <button type="submit" class="btn btn-outline-success w-100 fw-bold transition-hover">
                                                <i class="bi bi-send-check-fill me-2"></i>Solicitar Unirse
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <div class="col-12 text-center py-5">
                                <img src="../assets/img/no-orders-repartidor.png" alt="Todo listo" style="width: 150px; opacity: 0.6;" class="mb-3">
                                <h5 class="text-muted fw-bold">¡Estás al día!</h5>
                                <p class="text-muted">Ya has enviado solicitud a todos los restaurantes disponibles en tu zona.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // A) Cargar Pedidos Disponibles (Refresco automático)
        const container = document.getElementById('pedidos-disponibles-container');
        const cargarPedidos = () => {
            fetch('ajax_cargar_pedidos.php')
                .then(response => response.text())
                .then(html => {
                    container.innerHTML = html;
                })
                .catch(error => console.error('Error al cargar pedidos:', error));
        };
        cargarPedidos();
        setInterval(cargarPedidos, 5000);

        // B) Verificar Notificaciones de Nuevas Entregas
        const btnMisEntregas = document.getElementById('btn-mis-entregas');
        const checkNotificaciones = () => {
            fetch('ajax_check_notificaciones.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'nueva_entrega') {
                        // Cambiar botón a rojo y animar
                        btnMisEntregas.innerHTML = 'Ver Mis Entregas <span class="badge bg-danger ms-1">1</span>';
                        btnMisEntregas.classList.remove('btn-primary');
                        btnMisEntregas.classList.add('btn-danger', 'btn-pulse');
                        
                        // Marcar notificación como vista en la BD
                        fetch('../procesos/marcar_notificacion_vista.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id_solicitud: data.id_solicitud })
                        });
                        
                        // Opcional: Redirigir automáticamente
                        // setTimeout(() => { window.location.href = 'mis_entregas.php'; }, 2000);
                    }
                })
                .catch(error => console.error('Error al verificar notificaciones:', error));
        };
        setInterval(checkNotificaciones, 4000);
    });
</script>

<style>
    /* Animación para el botón de entregas */
    .btn-pulse {
        animation: pulse 1s infinite;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }
    .transition-hover:hover {
        transform: translateY(-2px);
        transition: transform 0.2s;
    }
</style>