<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit;
}

require_once '../config/conexion.php';
require_once '../models/Cliente.php'; // Carga las funciones planas
include 'layout/header.php';

$msg = "";
$error = "";

/* ==========================================
   1. LÓGICA DE ELIMINACIÓN (Usando función)
========================================== */
if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    
    // CAMBIO: Usamos la función eliminarCliente()
    if (eliminarCliente($id_eliminar)) {
        $msg = "🗑️ Cliente eliminado correctamente.";
    } else {
        $error = "No se puede eliminar: El cliente tiene ventas asociadas.";
    }
}

/* ==========================================
   2. CREAR O EDITAR (Usando funciones)
========================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $nombre = trim($_POST['nombre']);
    $dni    = trim($_POST['dni']);
    $id     = intval($_POST['id_cliente'] ?? 0);

    if (empty($nombre) || empty($dni)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (strlen($dni) !== 8 || !ctype_digit($dni)) {
        $error = "El DNI debe tener exactamente 8 dígitos numéricos.";
    } else {
        if ($_POST['accion'] === 'crear') {
            // CAMBIO: Usamos registrarCliente()
            if (registrarCliente($nombre, $dni)) {
                $msg = "✅ Cliente registrado con éxito.";
            } else {
                $error = "Error: Es posible que el DNI ya exista.";
            }
        } elseif ($_POST['accion'] === 'editar') {
            // CAMBIO: Usamos actualizarCliente()
            if (actualizarCliente($id, $nombre, $dni)) {
                $msg = "🔄 Cliente actualizado con éxito.";
            } else {
                $error = "Error al actualizar los datos.";
            }
        }
    }
}

/* ==========================================
   3. LISTADO Y BÚSQUEDA
========================================== */
$busqueda = trim($_GET['q'] ?? '');

// CAMBIO: Usamos listarClientes()
$todos_clientes = listarClientes(); 

// Filtrado simple en PHP
if ($busqueda !== "") {
    $clientes = array_filter($todos_clientes, function($c) use ($busqueda) {
        return stripos($c['nombre'], $busqueda) !== false || stripos($c['dni'], $busqueda) !== false;
    });
} else {
    $clientes = $todos_clientes;
}
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary"><i class="bi bi-people-fill"></i> Directorio de Clientes</h2>
            <p class="text-muted mb-0">Administra la base de datos de tus compradores.</p>
        </div>
        <button class="btn btn-primary shadow" onclick="abrirModalCrear()">
            <i class="bi bi-person-plus-fill"></i> Nuevo Cliente
        </button>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" placeholder="Buscar por Nombre o DNI..." value="<?= htmlspecialchars($busqueda) ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-dark w-100">Buscar</button>
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
                        <th>Nombre / Razón Social</th>
                        <th>DNI / RUC</th>
                        <th>Fecha Registro</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clientes)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No se encontraron clientes.</td></tr>
                    <?php else: ?>
                        <?php foreach ($clientes as $c): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-secondary">#<?= str_pad($c['id_cliente'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle p-2 me-3 text-primary">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <span class="fw-semibold"><?= htmlspecialchars($c['nombre']) ?></span>
                                </div>
                            </td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-dark border"><?= htmlspecialchars($c['dni']) ?></span></td>
                            <td class="text-muted small">
                                <?= isset($c['fecha_creacion']) ? date('d/m/Y', strtotime($c['fecha_creacion'])) : '-' ?>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-primary me-1" 
                                        onclick="abrirModalEditar(<?= $c['id_cliente'] ?>, '<?= htmlspecialchars($c['nombre']) ?>', '<?= htmlspecialchars($c['dni']) ?>')">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar(<?= $c['id_cliente'] ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCliente" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" autocomplete="off">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="modalTitulo">Gestionar Cliente</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="accion" id="inputAccion">
            <input type="hidden" name="id_cliente" id="inputId">
            
            <div class="mb-3">
                <label class="fw-bold form-label">Nombre Completo</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                    <input type="text" name="nombre" id="inputNombre" class="form-control" placeholder="Ej: Juan Pérez" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="fw-bold form-label">DNI (8 dígitos)</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-123"></i></span>
                    <input type="text" name="dni" id="inputDni" class="form-control" placeholder="Ej: 12345678" maxlength="8" pattern="\d{8}" required>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var modalElement = document.getElementById('modalCliente');
    var myModal = new bootstrap.Modal(modalElement);

    function abrirModalCrear() {
        document.getElementById('modalTitulo').innerText = "Nuevo Cliente";
        document.getElementById('modalTitulo').parentElement.classList.replace('bg-warning', 'bg-primary');
        document.getElementById('inputAccion').value = "crear";
        document.getElementById('inputId').value = "";
        document.getElementById('inputNombre').value = "";
        document.getElementById('inputDni').value = "";
        document.getElementById('btnGuardar').innerText = "Guardar Cliente";
        document.getElementById('btnGuardar').classList.replace('btn-warning', 'btn-primary');
        myModal.show();
    }

    function abrirModalEditar(id, nombre, dni) {
        document.getElementById('modalTitulo').innerText = "Editar Cliente";
        // Cambio visual para indicar edición
        document.getElementById('modalTitulo').parentElement.classList.replace('bg-primary', 'bg-warning');
        document.getElementById('modalTitulo').parentElement.classList.remove('text-white');
        
        document.getElementById('inputAccion').value = "editar";
        document.getElementById('inputId').value = id;
        document.getElementById('inputNombre').value = nombre;
        document.getElementById('inputDni').value = dni;
        document.getElementById('btnGuardar').innerText = "Actualizar Datos";
        document.getElementById('btnGuardar').classList.replace('btn-primary', 'btn-warning');
        
        myModal.show();
    }

    function confirmarEliminar(id) {
        Swal.fire({
            title: '¿Eliminar cliente?',
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `clientes.php?eliminar=${id}`;
            }
        });
    }
</script>

<?php include 'layout/footer.php'; ?>