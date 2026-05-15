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

    const cargarPedidos = () => {
        fetch('ajax_cargar_pedidos.php')
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
            })
            .catch(error => {
                console.error('Error al cargar pedidos:', error);
            });
    };

    // Cargar inmediatamente y luego cada 5 segundos
    cargarPedidos();
    setInterval(cargarPedidos, 5000);
});
</script>