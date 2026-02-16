<?php
session_start();
require_once 'includes/conexion.php'; // Necesario para obtener el teléfono

// El guardia de seguridad de la página
if (!isset($_SESSION['cliente_id'])) {
    header('Location: login_cliente.php');
    exit();
}

// 1. OBTENER TELÉFONO DEL CLIENTE (Para enviarlo oculto)
$id_cliente = $_SESSION['cliente_id'];
$sql_c = "SELECT telefono FROM usuarios_clientes WHERE id = ?";
$stmt_c = $conn->prepare($sql_c);
$stmt_c->bind_param("i", $id_cliente);
$stmt_c->execute();
$res_c = $stmt_c->get_result();
$datos_c = $res_c->fetch_assoc();
$telefono_cliente = $datos_c['telefono'] ?? ''; // Si no hay, queda vacío

include 'includes/header.php';
?>
<body>
<div class="hero-quickbite">
    <div class="container hero-text text-center">
        <h1 class="display-4 fw-bold">Ya casi está listo tu pedido</h1>
        <p class="lead text-white-50">Solo necesitamos unos datos más para la entrega.</p>
    </div>
</div>

<div class="main-content-overlay">
    <div class="container">

        <div class="mb-4">
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Seguir Comprando
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card checkout-card">
                    <div class="card-header">
                        <h4 class="mb-0">🛒 Resumen de tu Carrito</h4>
                    </div>
                    <div id="resumen-carrito" class="card-body p-0">
                        <div class="p-4 text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card checkout-card">
                    <div class="card-header">
                        <h4 class="mb-0">📝 Tus Datos para la Entrega</h4>
                    </div>
                    <div class="card-body">
                        <p class="lead fs-6">Hola, <strong><?php echo htmlspecialchars($_SESSION['cliente_nombre']); ?></strong>.</p>
                        
                        <form action="procesos/procesar_pedido.php" method="POST" id="checkout-form" enctype="multipart/form-data">
                            <input type="hidden" name="costo_envio" id="input_costo_envio">
                            <input type="hidden" name="carrito" id="carrito_data">
                            <input type="hidden" name="restaurante_id" id="id_restaurante"> <input type="hidden" name="latitud" id="latitud">
                            <input type="hidden" name="longitud" id="longitud">
                            
                            <input type="hidden" name="total" id="input_total">
                            <input type="hidden" name="telefono" value="<?php echo htmlspecialchars($telefono_cliente); ?>">
                            <input type="hidden" name="referencia" value="-"> <div class="mb-4">
                                <label class="form-label fw-bold"><i class="bi bi-geo-alt-fill me-2"></i>Ubicación de Entrega</label>
                                <div id="mapa-checkout" style="height: 250px; width: 100%; border-radius: 10px; margin-bottom: 10px;" class="border"></div>
                                <div class="form-text mb-2 text-primary"><i class="bi bi-info-circle"></i> Mueve el pin rojo para ajustar tu ubicación exacta.</div>
                                <div class="d-grid mb-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="usar-gps-btn">
                                        <i class="bi bi-crosshair me-1"></i> Detectar mi ubicación (GPS)
                                    </button>
                                    <div id="gps-status" class="form-text text-center"></div>
                                </div>
                                
                                <label for="direccion" class="form-label small text-muted">Dirección exacta / Referencia</label>
                                <textarea class="form-control" id="direccion" name="direccion" rows="2" required placeholder="Ej: Casa verde frente al parque..."></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold"><i class="bi bi-wallet2 me-2"></i>Método de Pago</label>
                                <select class="form-select mb-3" id="metodo_pago" name="metodo_pago" required>
                                    <option value="" selected disabled>Selecciona cómo pagar</option>
                                    <option value="yape">Yape / Plin</option>
                                    <option value="efectivo">Efectivo</option>
                                </select>

                                <div id="info-yape-container" class="card mb-3 border-primary" style="display: none; background-color: #f8f9fa;">
                                    <div class="card-body text-center">
                                        <h6 class="text-primary fw-bold mb-3"><i class="bi bi-qr-code-scan"></i> Escanea y Paga</h6>
                                        <div id="yape-qr-img-placeholder" class="mb-3 d-flex justify-content-center"></div>
                                        <p class="mb-1 text-muted small">Número asociado:</p>
                                        <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
                                            <h3 class="fw-bold mb-0 text-dark" id="yape-numero-display">...</h3>
                                            <button type="button" class="btn btn-outline-primary btn-sm rounded-circle" id="btn-copiar-yape"><i class="bi bi-clipboard-check"></i></button>
                                        </div>
                                        
                                        <div class="text-start bg-white p-3 rounded border">
                                            <label for="comprobante_yape" class="form-label small fw-bold text-dark">Sube tu captura del pago (Obligatorio)</label>
                                            <input type="file" class="form-control form-control-sm" id="comprobante_yape" name="evidencia_yape" accept="image/*">
                                            <div class="form-text small">El restaurante verificará esta imagen antes de preparar tu pedido.</div>
                                        </div>
                                    </div>
                                </div>

                                <div id="div-vuelto" style="display: none;">
                                    <label for="monto_pagar" class="form-label small">¿Con cuánto vas a pagar?</label>
                                    <div class="input-group">
                                        <span class="input-group-text">S/</span>
                                        <input type="number" class="form-control" id="monto_pagar" name="monto_pagar" placeholder="Ej: 50.00" step="0.10">
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg btn-confirm-order">Confirmar Pedido</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalBoleta" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-primary text-white" style="border-radius: 20px 20px 0 0;">
                <h5 class="modal-title fw-bold"><i class="bi bi-receipt me-2"></i>Confirma tu Pedido</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="mb-3 p-3 bg-white rounded shadow-sm border">
                    <h6 class="fw-bold text-primary mb-2">📍 Datos de Entrega</h6>
                    <p class="mb-1 small text-muted">Dirección / Referencia:</p>
                    <p class="mb-0 fw-bold text-dark" id="boleta-direccion">...</p>
                    <p class="mb-0 small text-muted mt-1">Tel: <?php echo htmlspecialchars($telefono_cliente); ?></p>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold text-primary mb-3">🧾 Resumen de Pago</h6>
                        <ul class="list-group list-group-flush mb-3" id="boleta-items"></ul>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal Productos:</span>
                            <span class="fw-bold" id="boleta-subtotal">S/ 0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Costo de Envío:</span>
                            <span class="fw-bold text-primary" id="boleta-envio">S/ 0.00</span>
                        </div>
                        <hr class="my-2 dashed">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 fw-bold text-dark mb-0">TOTAL A PAGAR:</span>
                            <span class="h4 fw-bold text-success mb-0" id="boleta-total">S/ 0.00</span>
                        </div>
                        <div class="mt-3 text-center">
                            <span class="badge bg-secondary" id="boleta-metodo">Método de Pago: ...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light justify-content-center pb-4">
                <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancelar / Editar</button>
                <button type="button" class="btn btn-success px-5 rounded-pill fw-bold shadow" id="btn-enviar-final">
                    <i class="bi bi-check-circle-fill me-2"></i> ¡Confirmar Pedido!
                </button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // =========================================================
    // 1. CONFIGURACIÓN Y VARIABLES GLOBALES
    // =========================================================
    // ID CLIENTE PHP
    const CLIENTE_ID = <?php echo $_SESSION['cliente_id']; ?>;

    const resumenDiv = document.getElementById('resumen-carrito');
    const carritoDataInput = document.getElementById('carrito_data');
    const restauranteIdInput = document.getElementById('id_restaurante');
    const totalInput = document.getElementById('input_total'); // NUEVO
    const checkoutForm = document.getElementById('checkout-form');
    
    // Elementos de Pago y Mapa
    const selectPago = document.getElementById('metodo_pago');
    const containerYape = document.getElementById('info-yape-container');
    const displayYapeNum = document.getElementById('yape-numero-display');
    const displayYapeQR = document.getElementById('yape-qr-img-placeholder');
    const btnCopiar = document.getElementById('btn-copiar-yape');
    const divVuelto = document.getElementById('div-vuelto');
    const inputVuelto = document.getElementById('monto_pagar');
    
    // Modal y Botones Finales
    const modalBoleta = new bootstrap.Modal(document.getElementById('modalBoleta'));
    const btnEnviarFinal = document.getElementById('btn-enviar-final');
    const btnConfirmarInicial = document.querySelector('.btn-confirm-order');

    // Datos del Carrito
    const carritoKey = `carritoData_${CLIENTE_ID}`;
    let carritoData = JSON.parse(sessionStorage.getItem(carritoKey)) || { items: [], restauranteId: null };
    let carrito = carritoData.items;

    // Datos Geográficos
    let datosRestaurante = { lat: null, lon: null, yapeNumero: '', yapeQR: '' };
    const defaultLat = -10.683; 
    const defaultLng = -76.256;
    let userLat = defaultLat;
    let userLng = defaultLng;

    // =========================================================
    // 2. INICIALIZAR MAPA
    // =========================================================
    if(document.getElementById('mapa-checkout')) {
        var mapa = L.map('mapa-checkout').setView([defaultLat, defaultLng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(mapa);
        var marcador = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(mapa);
    }

    // =========================================================
    // 3. FUNCIONES DEL CARRITO
    // =========================================================
    function renderCarrito() {
        resumenDiv.innerHTML = '';

        if (carrito.length === 0) {
            resumenDiv.innerHTML = '<div class="p-4 text-center text-muted">Tu carrito está vacío.</div>';
            checkoutForm.style.display = 'none';
            return;
        }

        checkoutForm.style.display = 'block';
        let totalProductos = 0;

        const responsiveWrapper = document.createElement('div');
        responsiveWrapper.className = 'table-responsive';

        const tabla = document.createElement('table');
        tabla.className = 'table table-borderless align-middle summary-table';
        tabla.innerHTML = `
            <thead class="table-light">
                <tr>
                    <th scope="col" class="ps-4">Producto</th>
                    <th scope="col" class="text-center">Cant.</th>
                    <th scope="col" class="text-end pe-4">Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot></tfoot>
        `;

        const tbody = tabla.querySelector('tbody');
        carrito.forEach(item => {
            const subtotal = item.cantidad * item.precio;
            totalProductos += subtotal;
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td class="ps-4">${item.nombre}</td>
                <td class="text-center">
                    <div class="input-group input-group-sm justify-content-center" style="width: 100px; margin:auto;">
                        <button class="btn btn-outline-secondary px-2" type="button" onclick="modificarCantidad('${item.id}', -1)">-</button>
                        <span class="input-group-text px-2">${item.cantidad}</span>
                        <button class="btn btn-outline-secondary px-2" type="button" onclick="modificarCantidad('${item.id}', 1)">+</button>
                    </div>
                </td>
                <td class="text-end pe-4">S/ ${subtotal.toFixed(2)}</td>
                <td class="text-center">
                    <button class="btn btn-outline-danger btn-sm rounded-circle" type="button" onclick="eliminarItem('${item.id}')"><i class="bi bi-trash"></i></button>
                </td>
            `;
            tbody.appendChild(fila);
        });

        const tfoot = tabla.querySelector('tfoot');
        tfoot.innerHTML = `
            <tr id="row-total-final" class="total-row border-top">
                <td colspan="2" class="text-end fw-bold ps-4">Total a Pagar</td>
                <td class="text-end fw-bold h5 pe-4" id="celda-total-final">S/ ${totalProductos.toFixed(2)}</td>
                <td></td>
            </tr>
        `;

        responsiveWrapper.appendChild(tabla);
        resumenDiv.appendChild(responsiveWrapper);

        // Actualizar inputs ocultos
        carritoDataInput.value = JSON.stringify(carrito);
        restauranteIdInput.value = carritoData.restauranteId;

        actualizarTotalesEnvio(); 
    }

    // Funciones globales
    window.modificarCantidad = function(idPlato, cambio) {
        const item = carrito.find(i => i.id === idPlato);
        if (item) {
            item.cantidad += cambio;
            if (item.cantidad <= 0) eliminarItem(idPlato);
            else guardarYRenderizar();
        }
    }

    window.eliminarItem = function(idPlato) {
        carrito = carrito.filter(i => i.id !== idPlato);
        guardarYRenderizar();
    }

    function guardarYRenderizar() {
        carritoData.items = carrito;
        sessionStorage.setItem(carritoKey, JSON.stringify(carritoData));
        renderCarrito();
    }

    // =========================================================
    // 4. LÓGICA DE PRECIOS Y ENVÍO
    // =========================================================
    if (carritoData && carritoData.restauranteId) {
        fetch(`procesos/obtener_datos_restaurante.php?id_restaurante=${carritoData.restauranteId}`)
            .then(response => response.json())
            .then(resp => {
                if (resp.status === 'success') {
                    datosRestaurante.lat = parseFloat(resp.data.latitud);
                    datosRestaurante.lon = parseFloat(resp.data.longitud);
                    datosRestaurante.yapeNumero = resp.data.yape_numero;
                    datosRestaurante.yapeQR = resp.data.yape_qr;
                    actualizarTotalesEnvio();
                }
            })
            .catch(err => console.error("Error datos restaurante:", err));
    }

    // =========================================================
// LÓGICA DE CÁLCULO DE ENVÍO (Dinámico por Restaurante)
// =========================================================

function calcularCostoEnvio(clienteLat, clienteLon) {
    // 1. Validar que tengamos la ubicación del restaurante
    if (!datosRestaurante.lat || !datosRestaurante.lon) {
        console.log("No hay coordenadas del restaurante, cobrando tarifa base.");
        return 5.00; 
    }

    // 2. Fórmula de Haversine para calcular distancia real en KM
    const R = 6371; 
    const dLat = (clienteLat - datosRestaurante.lat) * Math.PI / 180;
    const dLon = (clienteLon - datosRestaurante.lon) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(datosRestaurante.lat * Math.PI / 180) * Math.cos(clienteLat * Math.PI / 180) * Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    
    const distanciaKm = R * c; 
    console.log(`Distancia calculada: ${distanciaKm.toFixed(2)} km`);

    // 3. Tarifario
    let costo = 5.00; 

    if (distanciaKm > 1.5) { 
        // Si se pasa de 1.5 km, cobramos el exceso
        const kmAdicionales = distanciaKm - 1.5;
        // ¡AQUÍ BORRAMOS LA LÍNEA QUE DABA ERROR!
        costo += kmAdicionales * 2.00; 
    }

    return Math.round(costo * 10) / 10;
}

    function actualizarTotalesEnvio() {
    const costoEnvio = calcularCostoEnvio(userLat, userLng);
    
    // --- AGREGAR ESTA LÍNEA AQUÍ ---
    // Actualizamos el input oculto para que se envíe a la base de datos
    if(document.getElementById('input_costo_envio')) {
        document.getElementById('input_costo_envio').value = costoEnvio.toFixed(2);
    }
    // -------------------------------

    if(document.getElementById('latitud')) document.getElementById('latitud').value = userLat;
    if(document.getElementById('longitud')) document.getElementById('longitud').value = userLng;

    let subtotalProductos = 0;
    carrito.forEach(i => subtotalProductos += i.precio * i.cantidad);
    
    const totalFinal = subtotalProductos + costoEnvio;

    if(totalInput) totalInput.value = totalFinal.toFixed(2);

    const tfoot = document.querySelector('.summary-table tfoot');
    if(tfoot) {
        let rowEnvio = document.getElementById('row-costo-envio');
        const rowTotal = document.getElementById('row-total-final');
        
        if (!rowEnvio && rowTotal) {
            rowEnvio = document.createElement('tr');
            rowEnvio.id = 'row-costo-envio';
            rowEnvio.innerHTML = `<td colspan="2" class="text-end text-muted small pe-4">Costo de Envío (Distancia)</td><td class="text-end text-muted small pe-4" id="valor-envio"></td><td></td>`;
            tfoot.insertBefore(rowEnvio, rowTotal);
        }
        
        if (document.getElementById('valor-envio')) {
            document.getElementById('valor-envio').textContent = `S/ ${costoEnvio.toFixed(2)}`;
        }
        if (document.getElementById('celda-total-final')) {
            document.getElementById('celda-total-final').textContent = `S/ ${totalFinal.toFixed(2)}`;
        }
    }
    return costoEnvio;
}

    // =========================================================
    // 5. EVENTOS MAPA, GPS Y PAGO
    // =========================================================
    if(typeof marcador !== 'undefined'){
        marcador.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            userLat = pos.lat; userLng = pos.lng;
            actualizarTotalesEnvio();
        });
    }

    const btnGps = document.getElementById('usar-gps-btn');
    const gpsStatus = document.getElementById('gps-status');
    const dirInput = document.getElementById('direccion'); // Corregido ID

    if(btnGps) {
        btnGps.addEventListener('click', function() {
            if (navigator.geolocation) {
                gpsStatus.innerHTML = '<span class="spinner-border spinner-border-sm text-primary"></span> Buscando...';
                navigator.geolocation.getCurrentPosition(pos => {
                    userLat = pos.coords.latitude; userLng = pos.coords.longitude;
                    
                    if(typeof mapa !== 'undefined') {
                        mapa.setView([userLat, userLng], 16);
                        marcador.setLatLng([userLat, userLng]);
                    }
                    
                    dirInput.value = `Ubicación GPS (Lat: ${userLat.toFixed(4)}, Lon: ${userLng.toFixed(4)})`;
                    actualizarTotalesEnvio();
                    gpsStatus.innerHTML = '<span class="text-success fw-bold"><i class="bi bi-check-circle"></i> Ubicación actualizada</span>';
                }, err => {
                    gpsStatus.innerHTML = '<span class="text-danger">Error obteniendo ubicación.</span>';
                }, { enableHighAccuracy: true });
            }
        });
    }

    if(selectPago){
        selectPago.addEventListener('change', function() {
            containerYape.style.display = 'none';
            divVuelto.style.display = 'none';
            inputVuelto.removeAttribute('required');

            if (this.value === 'yape') {
                containerYape.style.display = 'block';
                displayYapeNum.textContent = datosRestaurante.yapeNumero || "No registrado";
                displayYapeQR.innerHTML = datosRestaurante.yapeQR ? 
                    `<img src="assets/img/qr/${datosRestaurante.yapeQR}" class="img-fluid rounded border" style="max-width: 200px;">` : 
                    '<span class="text-muted border p-2">Sin QR</span>';
            } else if (this.value === 'efectivo') {
                divVuelto.style.display = 'block';
                inputVuelto.setAttribute('required', 'true');
            }
            validarBotonConfirmacion();
        });
    }

    if(btnCopiar) {
        btnCopiar.addEventListener('click', function() {
            navigator.clipboard.writeText(displayYapeNum.textContent);
            alert("Número copiado");
        });
    }

    // =========================================================
    // 6. VALIDACIÓN Y MODAL
    // =========================================================
    const inputYapeFile = document.getElementById('comprobante_yape');

    function validarBotonConfirmacion() {
        if (!selectPago || !btnConfirmarInicial) return; 

        if (selectPago.value === 'yape') {
            if (inputYapeFile.files.length === 0) {
                btnConfirmarInicial.disabled = true;
                btnConfirmarInicial.innerHTML = '<i class="bi bi-camera-fill me-2"></i>Sube la captura para continuar';
                btnConfirmarInicial.classList.remove('btn-primary');
                btnConfirmarInicial.classList.add('btn-secondary');
            } else {
                btnConfirmarInicial.disabled = false;
                btnConfirmarInicial.innerHTML = 'Confirmar Pedido';
                btnConfirmarInicial.classList.remove('btn-secondary');
                btnConfirmarInicial.classList.add('btn-primary');
            }
        } else {
            btnConfirmarInicial.disabled = false;
            btnConfirmarInicial.innerHTML = 'Confirmar Pedido';
            btnConfirmarInicial.classList.remove('btn-secondary');
            btnConfirmarInicial.classList.add('btn-primary');
        }
    }

    if(inputYapeFile){
        inputYapeFile.addEventListener('change', validarBotonConfirmacion);
    }

    if(btnConfirmarInicial){
        btnConfirmarInicial.addEventListener('click', function(e) {
            e.preventDefault(); 

            // Validaciones
            const direccion = document.getElementById('direccion').value;
            const pago = selectPago.value;
            
            if (direccion.trim() === "" || pago === "") {
                alert("Por favor completa la dirección y el método de pago.");
                return;
            }
            if (pago === 'efectivo' && inputVuelto.value === "") {
                alert("Ingresa con cuánto vas a pagar.");
                return;
            }

            // Llenar Modal
            const costoEnvio = actualizarTotalesEnvio();
            let subtotal = 0;
            const listaItems = document.getElementById('boleta-items');
            listaItems.innerHTML = ''; 

            carrito.forEach(item => {
                const totalItem = item.precio * item.cantidad;
                subtotal += totalItem;
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center bg-transparent px-0 py-1';
                li.innerHTML = `<span class="small text-muted">${item.cantidad} x ${item.nombre}</span><span class="small">S/ ${totalItem.toFixed(2)}</span>`;
                listaItems.appendChild(li);
            });

            document.getElementById('boleta-direccion').textContent = direccion;
            document.getElementById('boleta-subtotal').textContent = `S/ ${subtotal.toFixed(2)}`;
            document.getElementById('boleta-envio').textContent = `S/ ${costoEnvio.toFixed(2)}`;
            document.getElementById('boleta-total').textContent = `S/ ${(subtotal + costoEnvio).toFixed(2)}`;
            
            let textoPago = pago === 'yape' ? 'Yape / Plin' : `Efectivo (Paga con S/${inputVuelto.value})`;
            document.getElementById('boleta-metodo').textContent = textoPago;

            modalBoleta.show();
        });
    }
    
    if(btnEnviarFinal){
        btnEnviarFinal.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Enviando...';
            checkoutForm.submit();
        });
    }

    // Iniciar
    renderCarrito();
    validarBotonConfirmacion();
    
    if(typeof mapa !== 'undefined') {
        setTimeout(() => { mapa.invalidateSize(); }, 500);
    }
});
</script>
<?php include 'includes/footer.php'; ?>