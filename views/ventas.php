<?php
session_start();

/* ==========================
   1. SEGURIDAD Y DEPENDENCIAS
========================================== */
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit;
}

require_once '../config/conexion.php';
require_once '../models/Producto.php';
require_once '../models/Cliente.php';
require_once '../models/Venta.php';
require_once 'layout/header.php';

/* ==========================
   2. OBTENER DATOS
========================================== */
$clientes  = listarClientes();
$productos = listarProductos();

$mensaje_server = "";
$tipo_mensaje   = "";

/* ==========================
   3. PROCESAR VENTA (BACKEND)
========================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente = intval($_POST['id_cliente'] ?? 0);
    $items_form = $_POST['items'] ?? []; 

    if ($id_cliente <= 0) {
        $mensaje_server = "Por favor seleccione un cliente.";
        $tipo_mensaje = "error";
    } elseif (empty($items_form)) {
        $mensaje_server = "El carrito de venta está vacío.";
        $tipo_mensaje = "error";
    } else {
        $detalleVenta = [];
        $error_stock = false;
        
        // Validar stock en el servidor antes de guardar
        foreach ($items_form as $id_prod => $cantidad) {
            $cantidad = intval($cantidad);
            if ($cantidad <= 0) continue;

            $prodData = buscarProducto($id_prod); // Función plana de Producto.php
            
            if (!$prodData || $cantidad > intval($prodData['stock'])) {
                $mensaje_server = "Stock insuficiente para el producto: " . ($prodData['nombre'] ?? 'Desconocido');
                $tipo_mensaje = "error";
                $error_stock = true;
                break;
            }

            $detalleVenta[] = [
                'id_producto' => $id_prod,
                'cantidad'    => $cantidad,
                'precio'      => floatval($prodData['precio'])
            ];
        }

        if (!$error_stock) {
            // Guardar Venta
            $id_venta = registrarVenta($id_cliente, $detalleVenta);

            if ($id_venta) {
                $mensaje_server = "Venta #$id_venta registrada exitosamente.";
                $tipo_mensaje = "success";
                // Recargar datos actualizados
                $productos = listarProductos(); 
            } else {
                $mensaje_server = "Error al registrar la venta en la base de datos.";
                $tipo_mensaje = "error";
            }
        }
    }
}
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    .tabla-scroll { max-height: 450px; overflow-y: auto; }
    .bg-total { background-color: #212529; color: #fff; font-size: 1.3rem; font-weight: bold; }
    .select2-container .select2-selection--single { height: 38px !important; }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered { line-height: 36px; }
</style>

<div class="container-fluid px-4 mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary"><i class="bi bi-cart4"></i> Nueva Venta</h2>
            <p class="text-muted mb-0">Punto de Venta (POS)</p>
        </div>
        <div class="text-end">
            <span class="badge bg-secondary fs-6 mb-1">Cajero: <?= $_SESSION['usuario'] ?></span>
            <div id="reloj" class="fw-bold text-dark">00:00:00</div>
        </div>
    </div>

    <div class="row">
        
        <div class="col-lg-4 mb-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-search"></i> Buscar Items</h5>
                </div>
                <div class="card-body bg-light">
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Cliente</label>
                        <select id="selectCliente" class="form-select select2">
                            <option value="">-- Buscar Cliente --</option>
                            <?php foreach ($clientes as $c): ?>
                                <option value="<?= $c['id_cliente'] ?>"><?= htmlspecialchars($c['nombre']) ?> (DNI: <?= $c['dni'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <div class="mt-1 text-end">
                            <a href="clientes.php" class="text-decoration-none small"><i class="bi bi-person-plus"></i> Nuevo cliente</a>
                        </div>
                    </div>

                    <hr class="text-muted">

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Producto</label>
                        <select id="selectProducto" class="form-select select2">
                            <option value="">-- Buscar producto por nombre --</option>
                            <?php foreach ($productos as $p): ?>
                                <?php if($p['stock'] > 0): ?>
                                    <option value="<?= $p['id_producto'] ?>" 
                                            data-precio="<?= $p['precio'] ?>" 
                                            data-stock="<?= $p['stock'] ?>"
                                            data-nombre="<?= htmlspecialchars($p['nombre']) ?>">
                                        <?= htmlspecialchars($p['nombre']) ?> | Stock: <?= $p['stock'] ?> | S/ <?= number_format($p['precio'], 2) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small">Stock Actual</label>
                            <input type="text" id="txtStock" class="form-control bg-white fw-bold text-center" readonly value="-" disabled>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small">Precio Unit.</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">S/</span>
                                <input type="text" id="txtPrecioDisplay" class="form-control bg-white fw-bold text-end border-start-0" readonly value="0.00" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Cantidad</label>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary" type="button" onclick="ajustarCantidad(-1)">-</button>
                            <input type="number" id="txtCantidad" class="form-control text-center fw-bold" min="1" value="1">
                            <button class="btn btn-outline-secondary" type="button" onclick="ajustarCantidad(1)">+</button>
                        </div>
                    </div>

                    <button type="button" class="btn btn-success w-100 py-2 shadow-sm" onclick="agregarAlCarrito()">
                        <i class="bi bi-cart-plus-fill"></i> AGREGAR AL CARRITO
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <form method="POST" id="formVenta" autocomplete="off">
                <input type="hidden" name="id_cliente" id="inputClienteHidden">

                <div class="card shadow border-0 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-primary fw-bold">Detalle de Venta</h5>
                        <span class="badge bg-light text-dark border" id="badgeItems">0 Ítems</span>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive tabla-scroll">
                            <table class="table table-striped table-hover align-middle mb-0">
                                <thead class="table-dark sticky-top">
                                    <tr>
                                        <th style="width: 40%;">Producto</th>
                                        <th class="text-center" style="width: 15%;">Cant.</th>
                                        <th class="text-end" style="width: 20%;">P. Unit.</th>
                                        <th class="text-end" style="width: 20%;">Subtotal</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyCarrito">
                                    <tr id="filaVacia">
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="bi bi-cart-x display-4 d-block mb-2"></i>
                                            El carrito está vacío
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer bg-total p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>TOTAL A PAGAR:</span>
                            <span id="txtTotal" class="text-warning">S/ 0.00</span>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="button" class="btn btn-outline-danger me-2" onclick="limpiarCarrito()">
                        <i class="bi bi-trash"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-primary btn-lg shadow px-5" onclick="confirmarVenta()">
                        <i class="bi bi-check-circle-fill"></i> FINALIZAR VENTA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // 1. INICIALIZACIÓN
    let carrito = []; 
    
    $(document).ready(function() {
        // Activar Select2 con tema Bootstrap 5
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        // Reloj
        setInterval(() => {
            const now = new Date();
            document.getElementById('reloj').innerText = now.toLocaleTimeString();
        }, 1000);

        // Mensajes del servidor (PHP)
        <?php if ($mensaje_server): ?>
            Swal.fire({
                icon: '<?= $tipo_mensaje ?>',
                title: '<?= $tipo_mensaje === "success" ? "¡Éxito!" : "Atención" ?>',
                text: '<?= $mensaje_server ?>'
            });
        <?php endif; ?>
    });

    // 2. DETECCIÓN DE CAMBIO EN PRODUCTO
    $('#selectProducto').on('select2:select', function (e) {
        // Select2 usa jQuery, así que usamos $(this)
        const option = $(this).find(':selected');
        
        if (option.val() !== "") {
            $('#txtStock').val(option.data('stock'));
            $('#txtPrecioDisplay').val(parseFloat(option.data('precio')).toFixed(2));
            $('#txtCantidad').val(1).attr('max', option.data('stock'));
        }
    });

    // 3. FUNCIONES DE INTERFAZ
    function ajustarCantidad(delta) {
        let input = document.getElementById('txtCantidad');
        let val = parseInt(input.value) || 0;
        let max = parseInt(document.getElementById('txtStock').value) || 9999;
        
        let nuevoVal = val + delta;
        if (nuevoVal >= 1 && nuevoVal <= max) {
            input.value = nuevoVal;
        }
    }

    function agregarAlCarrito() {
        // Obtener datos desde jQuery (por compatibilidad con Select2)
        const $option = $('#selectProducto').find(':selected');
        const id = $option.val();

        if (!id) { 
            Swal.fire('Error', 'Seleccione un producto primero', 'warning'); 
            return; 
        }
        
        const nombre = $option.data('nombre');
        const precio = parseFloat($option.data('precio'));
        const stock = parseInt($option.data('stock'));
        const cantidad = parseInt(document.getElementById('txtCantidad').value);

        if (isNaN(cantidad) || cantidad < 1) { 
            Swal.fire('Error', 'Cantidad inválida', 'warning'); 
            return; 
        }
        
        // Verificar duplicados y stock acumulado
        const existe = carrito.find(p => p.id === id);
        let cantidadFinal = cantidad;
        
        if (existe) {
            cantidadFinal += existe.cantidad;
        }

        if (cantidadFinal > stock) {
            Swal.fire('Stock Insuficiente', `Solo quedan ${stock} unidades y intentas agregar ${cantidadFinal}.`, 'error');
            return;
        }

        // Agregar o Actualizar
        if (existe) {
            existe.cantidad += cantidad;
            const Toast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 1500, timerProgressBar: true
            });
            Toast.fire({ icon: 'info', title: 'Cantidad actualizada' });
        } else {
            carrito.push({ id, nombre, precio, cantidad });
        }

        renderizarTabla();
        
        // Resetear inputs de producto
        $('#selectProducto').val(null).trigger('change');
        $('#txtStock').val('-');
        $('#txtPrecioDisplay').val('0.00');
        $('#txtCantidad').val(1);
    }

    function renderizarTabla() {
        const tbody = document.getElementById('tbodyCarrito');
        tbody.innerHTML = ""; 
        let totalGeneral = 0;

        if (carrito.length === 0) {
            tbody.innerHTML = `
                <tr id="filaVacia">
                    <td colspan="5" class="text-center text-muted py-5">
                        <i class="bi bi-cart-x display-4 d-block mb-2"></i>
                        El carrito está vacío
                    </td>
                </tr>`;
            document.getElementById('txtTotal').innerText = "S/ 0.00";
            document.getElementById('badgeItems').innerText = "0 Ítems";
            return;
        }

        carrito.forEach((prod, index) => {
            const subtotal = prod.cantidad * prod.precio;
            totalGeneral += subtotal;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div class="fw-bold text-dark">${prod.nombre}</div>
                    <input type="hidden" name="items[${prod.id}]" value="${prod.cantidad}">
                </td>
                <td class="text-center">
                    <span class="badge bg-light text-dark border fs-6">${prod.cantidad}</span>
                </td>
                <td class="text-end">S/ ${prod.precio.toFixed(2)}</td>
                <td class="text-end fw-bold">S/ ${subtotal.toFixed(2)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="eliminarItem(${index})" title="Eliminar">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('txtTotal').innerText = "S/ " + totalGeneral.toFixed(2);
        document.getElementById('badgeItems').innerText = carrito.length + " Ítems";
    }

    function eliminarItem(index) {
        carrito.splice(index, 1);
        renderizarTabla();
    }

    function limpiarCarrito() {
        if(carrito.length === 0) return;
        
        Swal.fire({
            title: '¿Vaciar carrito?',
            text: "Se eliminarán todos los productos agregados.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, vaciar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                carrito = [];
                renderizarTabla();
            }
        });
    }

    function confirmarVenta() {
        const cliente = $('#selectCliente').val();
        document.getElementById('inputClienteHidden').value = cliente;

        if (!cliente) { 
            Swal.fire('Falta Cliente', 'Por favor selecciona un cliente para la venta.', 'warning'); 
            return; 
        }
        if (carrito.length === 0) { 
            Swal.fire('Carrito Vacío', 'Agrega productos antes de finalizar.', 'warning'); 
            return; 
        }

        Swal.fire({
            title: '¿Finalizar Venta?',
            text: `Total a cobrar: ${document.getElementById('txtTotal').innerText}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Sí, cobrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formVenta').submit();
            }
        });
    }
</script>

<?php require_once 'layout/footer.php'; ?>