<?php
session_start();
if (!isset($_SESSION['restaurante_id'])) {
    header('Location: ../login_restaurante.php');
    exit();
}
require_once '../includes/conexion.php';
include '../includes/header.php';
?>

<div class="hero-quickbite">
    <div class="container hero-text">
        <div class="dashboard-header d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 fw-bold">Gestión de Pedidos</h1>
                <p class="lead text-white-50 mb-0">Revisa y actualiza el estado de tus pedidos aquí.</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-light mt-3 mt-md-0"><i class="bi bi-arrow-left me-2"></i>Volver al Panel</a>
        </div>
    </div>
</div>

<div class="main-content-overlay">
    <div class="container">
        <div id="pedidos-container">
            <div class="text-center p-5">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Cargando pedidos...</span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include '../includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('pedidos-container');

    // 1. SOLUCIÓN Z-INDEX: Mover modal al body al abrir y devolverlo al cerrar
    // Esto evita que la pantalla se ponga gris encima de la foto
    document.addEventListener('show.bs.modal', function (event) {
        const modal = event.target;
        document.body.appendChild(modal); // Lo mueve al final del body (capa superior)
    });

    document.addEventListener('hidden.bs.modal', function (event) {
        const modal = event.target;
        // Lo devolvemos al contenedor original para que el sistema de actualización
        // pueda borrarlo correctamente cuando refresque la lista.
        if(container) {
            container.appendChild(modal); 
        }
    });

    // 2. CARGA DE SOLICITUDES (Repartidores)
    const cargarSolicitudes = () => {
        const contenedoresSolicitudes = document.querySelectorAll('.solicitudes-container');
        contenedoresSolicitudes.forEach(contenedor => {
            const idPedido = contenedor.dataset.idPedido;
            if (idPedido) {
                fetch(`ajax_cargar_solicitudes.php?id_pedido=${idPedido}`)
                    .then(response => response.text())
                    .then(html => { contenedor.innerHTML = html; })
                    .catch(error => console.error('Error solicitudes:', error));
            }
        });
    };

    // 3. CARGA DE PEDIDOS (Con pausa inteligente)
    const cargarPedidos = () => {
        // Si hay una foto abierta, NO actualizamos nada para no cerrarla en la cara
        if (document.querySelector('.modal.show')) {
            console.log("Actualización pausada: Usuario viendo foto.");
            return; 
        }

        fetch('ajax_cargar_pedidos.php')
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
                cargarSolicitudes(); 
            })
            .catch(error => {
                console.error('Error pedidos:', error);
            });
    };

    // Iniciar
    cargarPedidos();
    setInterval(cargarPedidos, 6000);
});
</script>