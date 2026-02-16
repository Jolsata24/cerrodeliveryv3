<?php
session_start();
if (!isset($_SESSION['repartidor_id'])) {
    header('Location: ../login_repartidor.php');
    exit();
}
include '../includes/header.php';
?>

<div class="hero-quickbite">
    <div class="container hero-text">
        <div class="dashboard-header d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 fw-bold">Mis Entregas Activas</h1>
                <p class="lead text-white-50 mb-0">¡Manos a la obra! Estos son tus pedidos en curso.</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-light mt-3 mt-md-0"><i class="bi bi-arrow-left me-2"></i>Volver a Pedidos Disponibles</a>
        </div>
    </div>
</div>

<div class="main-content-overlay">
    <div class="container">
        <div id="entregas-container">
            <div class="text-center p-5">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Cargando entregas...</span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('entregas-container');
    
    const iniciarTracking = () => {
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    fetch('../repartidor/actualizar_ubicacion_repartidor.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ lat: lat, lon: lon })
                    });
                }, 
                function(error){ console.warn("Error de geolocalización:", error.message); },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }
    };

    const cargarEntregas = () => {
        fetch('ajax_cargar_mis_entregas.php')
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
                if (!html.includes("No tienes entregas activas")) {
                    iniciarTracking();
                }
            })
            .catch(error => console.error('Error al cargar entregas:', error));
    };

    cargarEntregas();
    setInterval(cargarEntregas, 7000);
});
</script>