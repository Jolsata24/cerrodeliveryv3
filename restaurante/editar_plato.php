<?php
session_start();
require_once '../includes/conexion.php';
include '../includes/header.php';

// Seguridad: Verificar sesión
if (!isset($_SESSION['restaurante_id'])) {
    header('Location: ../login_restaurante.php');
    exit();
}

// Verificar ID del plato
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$id_plato = $_GET['id'];
$id_restaurante = $_SESSION['restaurante_id'];

// Obtener datos actuales del plato
$sql = "SELECT * FROM menu_platos WHERE id = ? AND id_restaurante = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_plato, $id_restaurante);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    die("Plato no encontrado o acceso denegado.");
}

$plato = $resultado->fetch_assoc();
?>

<div class="hero-quickbite">
    <div class="container hero-text">
        <div class="dashboard-header d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 fw-bold">Editar Plato</h1>
                <p class="lead text-white-50 mb-0">Modificando: <?php echo htmlspecialchars($plato['nombre_plato']); ?></p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-light mt-3 mt-md-0">
                <i class="bi bi-arrow-left me-2"></i>Volver al Panel
            </a>
        </div>
    </div>
</div>

<div class="main-content-overlay">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card dashboard-card shadow-lg border-0">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h4 class="mb-0 text-primary fw-bold">Detalles del Plato</h4>
                    </div>
                    <div class="card-body p-4">
                        <form action="../procesos/actualizar_plato.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="id_plato" value="<?php echo $plato['id']; ?>">
                            
                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <label for="nombre_plato" class="form-label fw-bold text-muted">Nombre del Plato</label>
                                    <input type="text" class="form-control form-control-lg" name="nombre_plato" value="<?php echo htmlspecialchars($plato['nombre_plato']); ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="precio" class="form-label fw-bold text-muted">Precio (S/)</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light">S/</span>
                                        <input type="number" step="0.10" class="form-control fw-bold text-end" name="precio" value="<?php echo $plato['precio']; ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="descripcion" class="form-label fw-bold text-muted">Descripción</label>
                                <textarea class="form-control" name="descripcion" rows="3"><?php echo htmlspecialchars($plato['descripcion']); ?></textarea>
                            </div>

                            <div class="mb-4 p-3 bg-light rounded-3 border">
                                <label class="form-label fw-bold text-muted mb-3">Imagen del Plato</label>
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="position-relative">
                                            <img src="../assets/img/platos/<?php echo htmlspecialchars($plato['foto_url']); ?>" 
                                                 alt="Imagen actual" 
                                                 class="rounded-3 shadow-sm border"
                                                 style="width: 120px; height: 120px; object-fit: cover;">
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary">
                                                Actual
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <label for="foto" class="form-label text-primary fw-bold cursor-pointer">
                                            <i class="bi bi-cloud-upload-fill me-2"></i>Subir nueva imagen
                                        </label>
                                        <input class="form-control" type="file" name="foto" id="foto" accept="image/*">
                                        <div class="form-text mt-2"><i class="bi bi-info-circle"></i> Si no seleccionas nada, se mantendrá la foto actual.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 pt-2">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
                                    <i class="bi bi-save2-fill me-2"></i>Guardar Cambios
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$stmt->close();
$conn->close();
include '../includes/footer.php'; 
?>