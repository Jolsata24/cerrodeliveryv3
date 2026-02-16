<?php
session_start();
// Seguridad
if (!isset($_SESSION['cliente_id'])) {
    header('Location: login_cliente.php');
    exit();
}
require_once 'includes/conexion.php';
include 'includes/header.php';
?>

<div class="hero-quickbite">
    <div class="container hero-text text-center">
        <h1 class="display-5 fw-bold">Seguimiento de tus Pedidos</h1>
        <p class="lead text-white-50">Aquí puedes ver el historial y el estado actual de tus compras.</p>
    </div>
</div>

<div class="main-content-overlay">
    <div class="container">
        
        <div class="mb-4">
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Seguir Comprando
            </a>
        </div>

        <div id="historial-pedidos-container">
            <div class="text-center p-5">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Cargando historial...</span>
                </div>
            </div>
        </div>
        </div>
</div>
<?php
include 'includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('historial-pedidos-container');

    const cargarHistorial = () => {
        fetch('ajax_cargar_mis_pedidos.php')
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
            })
            .catch(error => console.error('Error al cargar el historial:', error));
    };

    // Carga inicial y luego cada 7 segundos
    cargarHistorial();
    setInterval(cargarHistorial, 7000);
});
</script>   