<?php
require_once 'includes/funciones.php';
session_start();

// Validamos sesión
if (!isset($_SESSION['cliente_id'])) {
    header("Location: login_cliente.php");
    exit();
}

require_once 'includes/conexion.php';

// Obtener datos
$id_cliente = $_SESSION['cliente_id'];
$sql = "SELECT nombre, email, telefono FROM usuarios_clientes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

require_once 'includes/header.php';

// Obtenemos la inicial para el avatar
$inicial = strtoupper(substr($usuario['nombre'], 0, 1));
?>

<style>
    /* --- ESTILOS PERSONALIZADOS CERRODELIVERY --- */
    .profile-header {
        /* Degradado Azul Noche Corporativo */
        background: linear-gradient(135deg, #222831 0%, #393e46 100%);
        height: 150px;
        border-radius: 20px 20px 0 0;
        position: relative;
        box-shadow: 0 4px 15px rgba(34, 40, 49, 0.2);
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        background-color: #fff;
        border: 4px solid #fff;
        border-radius: 50%;
        position: absolute;
        bottom: -60px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3.5rem;
        font-weight: 800;
        /* Letra en Naranja Ladrillo */
        color: #d65a31; 
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        transition: transform 0.3s ease;
    }
    
    .profile-avatar:hover {
        transform: translateX(-50%) scale(1.05);
    }

    .card-profile {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        overflow: visible;
        margin-top: 80px;
        background: #ffffff;
    }

    .form-floating > label {
        color: #6c757d;
        font-weight: 600;
    }

    /* Inputs al hacer foco: Borde y sombra Naranja */
    .form-control:focus {
        border-color: #d65a31;
        box-shadow: 0 0 0 0.25rem rgba(214, 90, 49, 0.2);
    }
    
    /* Botón Principal (Guardar) */
    .btn-cerro {
        background: linear-gradient(135deg, #d65a31 0%, #bf4d26 100%);
        border: none;
        color: white;
        transition: all 0.3s ease;
    }
    
    .btn-cerro:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(214, 90, 49, 0.4);
        color: white;
    }
    
    .badge-verificado {
        background-color: #f3f4f6 !important;
        color: #4b5563 !important;
        border: 1px solid #e5e7eb;
    }
    
    .text-primary-cerro {
        color: #d65a31 !important;
    }
</style>

<div class="container my-5 pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            <div class="card card-profile">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php echo $inicial; ?>
                    </div>
                </div>

                <div class="card-body pt-5 px-4 px-md-5">

                    <div class="text-center mt-5 mb-4">
                        <h3 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($usuario['nombre']); ?></h3>
                        <span class="badge badge-verificado rounded-pill px-3 py-2">
                            Cliente Verificado <i class="bi bi-patch-check-fill text-primary-cerro ms-1"></i>
                        </span>
                    </div>

                    <?php if (isset($_GET['msg'])): ?>
                        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 bg-success-subtle text-success-emphasis" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 bg-danger-subtle text-danger-emphasis" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="procesos/actualizar_perfil_cliente.php" method="POST">

                        <h6 class="text-muted mb-3 small fw-bold text-uppercase ls-1">Información Personal</h6>

                        <div class="form-floating mb-3">
                            <input type="email" class="form-control bg-light" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled readonly>
                            <label for="email"><i class="bi bi-envelope me-1"></i> Correo Electrónico</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                            <label for="nombre"><i class="bi bi-person me-1"></i> Nombre Completo</label>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="Teléfono" value="<?php echo htmlspecialchars($usuario['telefono']); ?>" required>
                            <label for="telefono"><i class="bi bi-phone me-1"></i> Teléfono / Celular</label>
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <h6 class="text-muted mb-3 small fw-bold text-uppercase ls-1">Seguridad</h6>

                        <div class="alert alert-light border d-flex align-items-center small rounded-3" role="alert">
                            <i class="bi bi-shield-lock-fill me-2 fs-5 text-muted"></i>
                            <div class="text-muted">Deja los campos de contraseña vacíos si no deseas cambiarla.</div>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="password_nueva" name="password_nueva" placeholder="Nueva Contraseña">
                            <label for="password_nueva"><i class="bi bi-key me-1"></i> Nueva Contraseña</label>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" placeholder="Confirmar">
                            <label for="password_confirmar"><i class="bi bi-key-fill me-1"></i> Confirmar Contraseña</label>
                        </div>

                        <div class="d-grid gap-2 mt-5">
                            <button type="submit" class="btn btn-cerro btn-lg rounded-pill fw-bold shadow-sm">
                                <i class="bi bi-save2 me-2"></i> Guardar Cambios
                            </button>
                            <a href="mis_pedidos.php" class="btn btn-outline-secondary rounded-pill border-0 py-2">
                                <i class="bi bi-arrow-left me-1"></i> Volver a mis pedidos
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center mt-4 text-muted small">
                ¿Necesitas ayuda?
                <a href="https://wa.me/51969704480?text=Hola,%20tengo%20un%20problema%20con%20mi%20cuenta"
                    target="_blank"
                    class="text-decoration-none fw-bold"
                    style="color: #d65a31;">
                    <i class="bi bi-whatsapp"></i> Contactar soporte por WhatsApp
                </a>
            </div>

        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>