<?php

include 'includes/header.php'; // <--- ESTO VA PRIMERO
include 'includes/conexion.php'; // <--- ESTO VA DESPUÉS

// ... el resto del código sigue igual ...


// --- 1. OBTENER TODAS LAS CATEGORÍAS (Lógica sin cambios) ---
$sql_categorias = "SELECT * FROM categorias ORDER BY nombre_categoria ASC";
$resultado_categorias_query = $conn->query($sql_categorias);
$categorias_data = $resultado_categorias_query->fetch_all(MYSQLI_ASSOC);


// --- 2. LÓGICA DE FILTRADO DE RESTAURANTES (Lógica sin cambios) ---
date_default_timezone_set('America/Lima');
$hora_actual = date('H:i:s');
$termino_busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
$categoria_seleccionada_id = isset($_GET['categoria_id']) && is_numeric($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : 0;
$nombre_categoria_actual = "Restaurantes Disponibles";

// Se añade r.imagen_fondo a la consulta (Lógica sin cambios)
$sql = "SELECT DISTINCT r.id, r.nombre_restaurante, r.direccion, r.puntuacion_promedio, r.total_puntuaciones, r.hora_apertura, r.hora_cierre, r.imagen_fondo 
        FROM restaurantes r";
if ($categoria_seleccionada_id > 0) {
    $sql .= " JOIN restaurante_categorias rc ON r.id = rc.id_restaurante";
}
$sql .= " WHERE r.estado = 'activo' AND r.fecha_vencimiento_suscripcion >= CURDATE()";
$params = [];
$types = '';
if ($categoria_seleccionada_id > 0) {
    $sql .= " AND rc.id_categoria = ?";
    $params[] = $categoria_seleccionada_id;
    $types .= 'i';
    $stmt_cat_nombre = $conn->prepare("SELECT nombre_categoria FROM categorias WHERE id = ?");
    $stmt_cat_nombre->bind_param("i", $categoria_seleccionada_id);
    $stmt_cat_nombre->execute();
    $res_cat = $stmt_cat_nombre->get_result();
    if ($cat_row = $res_cat->fetch_assoc()) {
        $nombre_categoria_actual = "Restaurantes de " . $cat_row['nombre_categoria'];
    }
    $stmt_cat_nombre->close();
}
if ($termino_busqueda) {
    $sql .= " AND r.nombre_restaurante LIKE ?";
    $params[] = "%" . $termino_busqueda . "%";
    $types .= 's';
}
$sql .= " ORDER BY r.puntuacion_promedio DESC, r.nombre_restaurante ASC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultado = $stmt->get_result();

// --- 3. MAPEO DE IMÁGENES LOCALES (Lógica sin cambios) ---
$imagenes_locales = [
    'hamburguesas' => 'hamburguesas.png',
    'polloalabrasa' => 'polloalabrasa.png',
    'chaufas' => 'chaufa.png',
    'broaster' => 'broaster.png',
    'salchipapas' => 'salchipapa.png',
    'mariscos' => 'mariscos.png',
];
// =====================================
// FIN DE LÓGICA PHP (INTACTA)
// =====================================
?>

<div class="hero-quickbite">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 hero-text">
                <h1 class="display-3 fw-bold">
                    El sabor que abriga a <span style="color: var(--primary-color);">4,380 m.s.n.m.</span>
                </h1>
                <p class="lead my-4 text-light opacity-75">
                    ¿Mucho frío en Chaupimarca o Yanacancha? Nosotros te llevamos tus platos favoritos calentitos hasta tu puerta.
                </p>
                <div class="d-flex gap-3">
                    <a href="#restaurantes-section" class="btn btn-order-now btn-lg shadow">
                        <i class="bi bi-fire me-2"></i> ¡Pedir Ahora!
                    </a>
                </div>
            </div>
            <div class="col-lg-6 hero-image text-center">
                <img src="assets/img/fondov1.png" class="img-fluid" alt="Delivery en Cerro de Pasco">
            </div>
        </div>
    </div>
</div>

<div class="main-content-overlay">

    <div class="container category-section mb-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <h2 class="fw-bold mb-0">¿Qué se te antoja hoy?</h2>
            <?php if ($categoria_seleccionada_id > 0): ?>
                <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-pill">
                    <i class="bi bi-x-lg"></i> Ver todas
                </a>
            <?php endif; ?>
        </div>

        <div class="scroller" data-speed="slow">
            <ul class="tag-list scroller__inner">
                <?php
                // Duplicamos el array para el efecto de scroll infinito
                $categorias_combinadas = array_merge($categorias_data, $categorias_data);

                foreach ($categorias_combinadas as $categoria):
                    // Lógica para asignar imagen según el nombre (se mantiene igual)
                    $key_imagen = strtolower(str_replace(' ', '', $categoria['nombre_categoria']));
                    $nombre_imagen = $imagenes_locales[$key_imagen] ?? 'default.png';

                    // Determinar si esta categoría está activa para resaltarla visualmente
                    $clase_activa = ($categoria['id'] == $categoria_seleccionada_id) ? 'border-primary bg-light' : '';
                ?>
                    <li>
                        <a href="index.php?categoria_id=<?php echo $categoria['id']; ?>#restaurantes-section" class="category-card-link text-decoration-none">
                            <div class="card category-card-v2 h-100 <?php echo $clase_activa; ?>">
                                <img src="assets/img/categorias/<?php echo $nombre_imagen; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($categoria['nombre_categoria']); ?>">
                                <div class="card-body text-center">
                                    <h6 class="card-title fw-bold mb-0 text-dark"><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></h6>
                                </div>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="container" id="restaurantes-section">
        <h2 class="fw-bold mb-4"><?php echo $nombre_categoria_actual; // Lógica sin cambios 
                                    ?></h2>

        <form action="index.php" method="GET" class="mb-4">
            <div class="input-group input-group-lg">
                <input class="form-control" type="search" placeholder="Busca tu restaurante preferido..." name="q" value="<?php echo htmlspecialchars($termino_busqueda); // Lógica sin cambios 
                                                                                                                            ?>">
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
            </div>
        </form>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
            <?php if ($resultado->num_rows > 0): // Lógica sin cambios 
            ?>
                <?php while ($restaurante = $resultado->fetch_assoc()): // Lógica sin cambios 
                ?>
                    <?php
                    // Lógica para verificar si está abierto (sin cambios)
                    $esta_abierto = false;
                    if (!empty($restaurante['hora_apertura']) && !empty($restaurante['hora_cierre'])) {
                        $apertura = $restaurante['hora_apertura'];
                        $cierre = $restaurante['hora_cierre'];
                        if ($apertura < $cierre) {
                            if ($hora_actual >= $apertura && $hora_actual <= $cierre) $esta_abierto = true;
                        } else {
                            if ($hora_actual >= $apertura || $hora_actual <= $cierre) $esta_abierto = true;
                        }
                    }
                    ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm card-restaurant">
                            <img src="assets/img/restaurantes/<?php echo htmlspecialchars($restaurante['imagen_fondo']); ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?php echo htmlspecialchars($restaurante['nombre_restaurante']); ?>">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="card-title fw-bold"><?php echo htmlspecialchars($restaurante['nombre_restaurante']); ?></h5>
                                    <span class="badge <?php echo $esta_abierto ? 'bg-success' : 'bg-danger'; ?> ms-2 flex-shrink-0"><?php echo $esta_abierto ? 'Abierto' : 'Cerrado'; ?></span>
                                </div>
                                <p class="card-text text-muted small mb-2"><?php echo htmlspecialchars($restaurante['direccion']); ?></p>
                                <div class="mt-auto">
                                    <?php
                                    // Lógica de estrellas (sin cambios)
                                    $promedio = round($restaurante['puntuacion_promedio'] ?? 0);
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo ($i <= $promedio) ? '⭐' : '☆';
                                    }
                                    ?>
                                    <span class="ms-1 small">(<?php echo $restaurante['total_puntuaciones'] ?? 0; ?>)</span>
                                </div>

                                <?php if ($esta_abierto): // Lógica sin cambios 
                                ?>
                                    <a href="menu_publico.php?id=<?php echo $restaurante['id']; ?>" class="stretched-link"></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: // Lógica sin cambios 
            ?>
                <div class="col-12">
                    <div class="alert alert-warning text-center">No se encontraron restaurantes que coincidan con tu búsqueda.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="container my-5">
    <div class="location-banner-pasco position-relative overflow-hidden rounded-4 p-5 text-center text-white" style="background: url('assets/img/pasco.png') center/cover no-repeat;">
        <div style="position: absolute; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.6); z-index:1;"></div>

        <div class="location-banner-content position-relative" style="z-index: 2;">
            <i class="bi bi-geo-alt-fill display-4 mb-3 d-block text-warning"></i>
            <h2 class="display-5 fw-bold mt-3">¡La Ciudad Real de Minas tiene su propio Delivery!</h2>
            <p class="lead mx-auto" style="max-width: 700px;">
                Conectamos los mejores sabores de la ciudad más alta del mundo contigo.
                Desde San Juan hasta la Esperanza, cubrimos todo Cerro de Pasco.
            </p>
        </div>
    </div>
</div>
<div class="container section-cta py-5 mt-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Forma Parte de CerroDelivery</h2>
        <p class="lead text-muted">Ingresa a tu panel o únete a nuestra plataforma.</p>
    </div>
    <div class="row g-4">

        <div class="col-md-4">
            <div class="card cta-card h-100">
                <img src="assets/img/usuario.jpg" class="card-img-top" alt="Cliente pidiendo comida">
                <div class="card-body text-center d-flex flex-column">
                    <h5 class="card-title">¿Tienes Hambre?</h5>
                    <p class="card-text text-muted">Ingresa y descubre los mejores sabores de la ciudad. Tus platos favoritos te esperan.</p>
                    <a href="login_cliente.php" class="btn btn-primary mt-auto">Ingresar como Cliente</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card cta-card h-100">
                <img src="assets/img/dueño.jpg" class="card-img-top" alt="Dueño de restaurante">
                <div class="card-body text-center d-flex flex-column">
                    <h5 class_s="card-title">¿Tienes un Negocio?</h5>
                    <p class="card-text text-muted">Gestiona tus pedidos, actualiza tu menú y llega a más clientes que nunca.</p>
                    <a href="login_restaurante.php" class="btn btn-success mt-auto">Acceso Socios</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card cta-card h-100">
                <img src="assets/img/repartidor.jpg" class="card-img-top" alt="Repartidor en moto">
                <div class="card-body text-center d-flex flex-column">
                    <h5 class="card-title">¿Quieres Repartir?</h5>
                    <p class="card-text text-muted">Conéctate, acepta pedidos y genera ganancias en tu propio horario.</p>
                    <a href="login_repartidor.php" class="btn btn-info text-white mt-auto">Acceso Repartidores</a>
                </div>
            </div>
        </div>
    </div>
</div>
</div> <?php
        // =====================================
        // CIERRE DE PHP (SIN CAMBIOS)
        // =====================================
        $stmt->close();
        $conn->close();
        include 'includes/footer.php';
        ?>