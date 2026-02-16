<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- LÓGICA DE RUTAS DINÁMICAS (CORRECCIÓN 1) ---
// Detectamos en qué carpeta estamos para saber si necesitamos subir un nivel (../)
$directorio_actual = basename(dirname($_SERVER['PHP_SELF']));
$subcarpetas = ['restaurante', 'repartidor', 'admin', 'procesos'];
$ruta_base = in_array($directorio_actual, $subcarpetas) ? '../' : '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Pide comida a domicilio en Cerro de Pasco con CerroDelivery. Hamburguesas, chaufas, pollerías y más. ¡El sabor que abriga a 4,380 m.s.n.m.!">
    <meta name="google-site-verification" content="google-site-verification=uXArrWaMfiSlMMwHjXePgJ508lFWXIAJ5iE5eNEYBrA" />
<title>CerroDelivery - Delivery de Comida en Cerro de Pasco</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" href="<?php echo $ruta_base; ?>assets/img/logoheader.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="<?php echo $ruta_base; ?>assets/css/style.css">

    <?php
    // --- LÓGICA DE SELECCIÓN DE CSS ESPECÍFICO (CORRECCIÓN 2) ---
    $script_name = basename($_SERVER['SCRIPT_NAME']);

    // 1. Archivos de la RAÍZ
    if ($script_name == 'index.php') {
        echo '<link rel="stylesheet" href="' . $ruta_base . 'assets/css/index.css">';
    } elseif ($script_name == 'menu_publico.php') {
        echo '<link rel="stylesheet" href="' . $ruta_base . 'assets/css/menu_publico.css">';
    } elseif ($script_name == 'checkout.php') {
        echo '<link rel="stylesheet" href="' . $ruta_base . 'assets/css/checkout.css">';
    } elseif ($script_name == 'mis_pedidos.php') {
        echo '<link rel="stylesheet" href="' . $ruta_base . 'assets/css/mis_pedidos.css">';
    } elseif (in_array($script_name, ['login_cliente.php', 'login_repartidor.php', 'login_restaurante.php'])) {
        echo '<link rel="stylesheet" href="' . $ruta_base . 'assets/css/login.css">';
    } elseif ($script_name == 'rastrear_pedido.php') {
        echo '<link rel="stylesheet" href="' . $ruta_base . 'assets/css/rastrear-pedido.css">';
    }

    // 2. Archivos del RESTAURANTE
    elseif ($directorio_actual == 'restaurante') {
        if ($script_name == 'dashboard.php') {
            echo '<link rel="stylesheet" href="' . $ruta_base . 'assets/css/restaurante-dashboard.css">';
        } elseif ($script_name == 'pedidos.php') {
            echo '<link rel="stylesheet" href="' . $ruta_base . 'assets/css/restaurante-pedidos.css">';
        } elseif ($script_name == 'editar_plato.php') {
            // Reutilizamos el estilo del dashboard para que tenga el mismo fondo
            echo '<link rel="stylesheet" href="' . $ruta_base . 'assets/css/restaurante-dashboard.css">';
        }
    }

    // 3. Archivos del REPARTIDOR
    elseif ($directorio_actual == 'repartidor') {
        if ($script_name == 'dashboard.php') {
            echo '<link rel="stylesheet" href="' . $ruta_base . 'assets/css/repartidor-dashboard.css">';
        } elseif ($script_name == 'mis_entregas.php') {
            echo '<link rel="stylesheet" href="' . $ruta_base . 'assets/css/repartidor-entregas.css">';
        }
    }
    ?>


    <script>
        const CLIENTE_ID = <?php echo isset($_SESSION['cliente_id']) ? json_encode($_SESSION['cliente_id']) : 'null'; ?>;
    </script>
</head>

<body>
    <div id="preloader">
        <img src="<?php echo $ruta_base; ?>assets/img/loader.gif" alt="Cargando..." style="width: 200px;">
    </div>

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $ruta_base; ?>index.php">
                <img src="<?php echo $ruta_base; ?>assets/img/logo.png" alt="CerroDelivery" class="navbar-logo" style="height: 45px;">
            </a>

            <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link text-white" href="<?php echo $ruta_base; ?>index.php">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="<?php echo $ruta_base; ?>index.php#restaurantes-section">Restaurantes</a></li>
                    <?php if (isset($_SESSION['cliente_id'])): ?>
                        <li class="nav-item"><a class="nav-link text-white" href="<?php echo $ruta_base; ?>mis_pedidos.php">Mis Pedidos</a></li>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav align-items-lg-center gap-2">
                    <?php if (isset($_SESSION['cliente_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white fw-bold" href="#" role="button" data-bs-toggle="dropdown">
                                Hola, <?php echo htmlspecialchars($_SESSION['cliente_nombre']); ?>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="mis_pedidos.php">Mis Pedidos</a></li>

                                <li><a class="dropdown-item" href="mi_perfil.php">Mi Perfil</a></li>

                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="procesos/logout_cliente.php">Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <?php if (isset($_SESSION['restaurante_id'])): ?>
                            <li class="nav-item"><a class="nav-link text-white" href="<?php echo $ruta_base; ?>restaurante/dashboard.php">Mi Restaurante</a></li>
                        <?php elseif (isset($_SESSION['repartidor_id'])): ?>
                            <li class="nav-item"><a class="nav-link text-white" href="<?php echo $ruta_base; ?>repartidor/dashboard.php">Soy Repartidor</a></li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a href="<?php echo $ruta_base; ?>login_cliente.php" class="btn btn-outline-light btn-sm px-4 rounded-pill">Ingresar</a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo $ruta_base; ?>registro_cliente.php" class="btn btn-light text-primary btn-sm px-4 rounded-pill fw-bold">Registrarse</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (basename($_SERVER['PHP_SELF']) != 'index.php'): ?>
        <main class="container mt-4">
        <?php endif; ?>