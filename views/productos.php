<?php
session_start();
/* ==========================================
   1. SEGURIDAD: VERIFICAR SESIÓN
========================================== */
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit;
}

/* ==========================================
   2. DEPENDENCIAS
========================================== */
require_once '../config/conexion.php';
require_once '../models/Producto.php'; 
include 'layout/header.php';

$success = "";
$error   = "";

/* ==========================================
   3. LÓGICA: GUARDAR (CREAR / EDITAR)
========================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

    $nombre = trim($_POST['nombre']);
    $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
    $stock  = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
    $id     = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT);

    if (empty($nombre) || $precio === false || $stock === false || $precio < 0 || $stock < 0) {
        $error = "❌ Verifique los datos. El precio y stock no pueden ser negativos.";
    } else {
        try {
            if ($_POST['accion'] === 'crear') {
                if (registrarProducto($nombre, $precio, $stock)) {
                    $success = "✅ Producto registrado correctamente.";
                } else {
                    $error = "❌ Error al registrar (posible duplicado).";
                }
            } 
            elseif ($_POST['accion'] === 'editar' && $id) {
                if (editarProducto($id, $nombre, $precio, $stock)) {
                    $success = "🔄 Producto actualizado correctamente.";
                } else {
                    $error = "❌ Error al actualizar.";
                }
            }
        } catch (Exception $e) {
            $error = "Ocurrió un error en el servidor.";
        }
    }
}

/* ==========================================
   4. LÓGICA: ELIMINAR
========================================== */
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    if (eliminarProducto($id)) {
        $success = "🗑️ Producto eliminado del catálogo.";
    } else {
        $error = "⚠️ No se puede eliminar: El producto tiene ventas registradas.";
    }
}

/* ==========================================
   5. LISTADO, BÚSQUEDA Y KPIs
========================================== */
$todosProductos = listarProductos();
$busqueda = trim($_GET['q'] ?? '');

// Filtrar
if ($busqueda !== "") {
    $productos = array_filter($todosProductos, function($p) use ($busqueda) {
        return stripos($p['nombre'], $busqueda) !== false;
    });
} else {
    $productos = $todosProductos;
}

// KPIs
$valorInventario = 0;
$stockBajo = 0;
foreach ($todosProductos as $p) {
    $valorInventario += ($p['precio'] * $p['stock']);
    if ($p['stock'] <= 10) $stockBajo++;
}
?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark"><i class="bi bi-box-seam"></i> Gestión de Inventario</h2>
            <p class="text-muted mb-0">Administración de productos y stock.</p>
        </div>
        <button class="btn btn-primary shadow" onclick="abrirModalCrear()">
            <i class="bi bi-plus-lg"></i> Nuevo Producto
        </button>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-0 opacity-75">Total Items</h6>
                        <h2 class="fw-bold mb-0"><?= count($todosProductos) ?></h2>
                    </div>
                    <i class="bi bi-tags fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-0 opacity-75">Valorización</h6>
                        <h2 class="fw-bold mb-0">S/ <?= number_format($valorInventario, 2) ?></h2>
                    </div>
                    <i class="bi bi-cash-coin fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm <?= $stockBajo > 0 ? 'bg-danger' : 'bg-secondary' ?> text-white h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-0 opacity-75">Stock Crítico</h6>
                        <h2 class="fw-bold mb-0"><?= $stockBajo ?> alertas</h2>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="bi bi-exclamation-octagon-fill"></i> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-2">
            <form method="GET" class="row g-0">
                <div class="col-md-10">
                    <input type="text" name="q" class="form-control border-0" placeholder="🔍 Buscar producto por nombre..." value="<?= htmlspecialchars($busqueda) ?>">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-dark w-100">Buscar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow border-0 rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($productos)): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">No se encontraron productos.</td></tr>
                    <?php else: ?>
                        <?php foreach ($productos as $p): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-light text-secondary border">#<?= str_pad($p['id_producto'], 3, '0', STR_PAD_LEFT) ?></span>
                                </td>
                                <td class="fw-bold text-dark">
                                    <?= htmlspecialchars($p['nombre']) ?>
                                </td>
                                <td>S/ <?= number_format($p['precio'], 2) ?></td>
                                <td>
                                    <span class="fw-bold <?= $p['stock'] <= 10 ? 'text-danger' : 'text-dark' ?>">
                                        <?= $p['stock'] ?> u.
                                    </span>
                                </td>
                                <td>
                                    <?php if ($p['stock'] == 0): ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-2">Agotado</span>
                                    <?php elseif ($p['stock'] <= 10): ?>
                                        <span class="badge bg-warning bg-opacity-10 text-dark px-2">Bajo</span>
                                    <?php else: ?>
                                        <span class="badge bg-success bg-opacity-10 text-success px-2">Disponible</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm" role="group">
                                        
                                        <button type="button" class="btn btn-sm btn-warning text-white" 
                                                data-bs-toggle="tooltip" title="Editar"
                                                data-id="<?= $p['id_producto'] ?>"
                                                data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                                                data-precio="<?= $p['precio'] ?>"
                                                data-stock="<?= $p['stock'] ?>"
                                                onclick="abrirModalEditar(this)">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                data-bs-toggle="tooltip" title="Eliminar"
                                                onclick="confirmarEliminar(<?= $p['id_producto'] ?>, '<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalProducto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalTitulo">Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" autocomplete="off">
                <div class="modal-body">
                    <input type="hidden" name="accion" id="inputAccion">
                    <input type="hidden" name="id_producto" id="inputId">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre del Producto</label>
                        <input type="text" name="nombre" id="inputNombre" class="form-control" required placeholder="Ej: Martillo de uña">
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Precio (S/)</label>
                            <input type="number" step="0.01" name="precio" id="inputPrecio" class="form-control" required min="0" placeholder="0.00">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label fw-bold">Stock</label>
                            <input type="number" name="stock" id="inputStock" class="form-control" required min="0" placeholder="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Usamos DOMContentLoaded para asegurar que el HTML existe antes de ejecutar JS
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. Inicializar Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        // 2. Preparar el Modal
        var modalEl = document.getElementById('modalProducto');
        if (modalEl) {
            var myModal = new bootstrap.Modal(modalEl);

            // ============================================
            // FUNCIÓN CREAR (Global para que el botón HTML la vea)
            // ============================================
            window.abrirModalCrear = function() {
                document.getElementById('modalTitulo').innerText = "Nuevo Producto";
                document.getElementById('modalTitulo').parentElement.classList.remove('bg-primary');
                document.getElementById('modalTitulo').parentElement.classList.add('bg-dark');
                
                document.getElementById('inputAccion').value = "crear";
                document.getElementById('inputId').value = "";
                document.getElementById('inputNombre').value = "";
                document.getElementById('inputPrecio').value = "";
                document.getElementById('inputStock').value = "";
                
                let btn = document.getElementById('btnGuardar');
                btn.innerText = "Guardar Producto";
                btn.classList.remove('btn-warning');
                btn.classList.add('btn-primary');
                
                myModal.show();
            };

            // ============================================
            // FUNCIÓN EDITAR (Global)
            // ============================================
            window.abrirModalEditar = function(boton) {
                // Recuperar datos seguros del botón
                let id = boton.getAttribute('data-id');
                let nombre = boton.getAttribute('data-nombre');
                let precio = boton.getAttribute('data-precio');
                let stock = boton.getAttribute('data-stock');

                document.getElementById('modalTitulo').innerText = "Editar Producto";
                document.getElementById('modalTitulo').parentElement.classList.remove('bg-dark');
                document.getElementById('modalTitulo').parentElement.classList.add('bg-primary'); // Cambio de color a azul
                
                document.getElementById('inputAccion').value = "editar";
                document.getElementById('inputId').value = id;
                document.getElementById('inputNombre').value = nombre;
                document.getElementById('inputPrecio').value = precio;
                document.getElementById('inputStock').value = stock;
                
                let btn = document.getElementById('btnGuardar');
                btn.innerText = "Actualizar";
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-warning');

                myModal.show();
            };
        }
    });

    // 3. FUNCIÓN ELIMINAR
    function confirmarEliminar(id, nombre) {
        Swal.fire({
            title: '¿Eliminar producto?',
            text: `Se eliminará "${nombre}" permanentemente.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `productos.php?eliminar=${id}`;
            }
        });
    }

    // 4. CERRAR ALERTAS AUTOMÁTICAMENTE
    setTimeout(() => {
        let alertas = document.querySelectorAll('.alert');
        alertas.forEach(a => {
            let alertInstance = new bootstrap.Alert(a);
            alertInstance.close();
        });
    }, 4000);
</script>

<?php include 'layout/footer.php'; ?>