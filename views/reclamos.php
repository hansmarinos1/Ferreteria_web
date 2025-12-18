<?php
session_start();

/* ===========================
   SEGURIDAD DE SESIÓN
=========================== */
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit;
}

require '../config/conexion.php';
include 'layout/header.php';

$success = "";
$error   = "";

/* ===========================
   LÓGICA: REGISTRAR (POST)
=========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'registrar') {
    $cliente     = trim($_POST['cliente']);
    $descripcion = trim($_POST['descripcion']);
    
    // Validación básica
    if (empty($cliente) || empty($descripcion)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (strlen($descripcion) < 10) {
        $error = "La descripción debe ser más detallada (mínimo 10 caracteres).";
    } else {
        try {
            $stmt = $conexion->prepare("INSERT INTO reclamos (cliente, descripcion, fecha, estado) VALUES (?, ?, NOW(), 'Pendiente')");
            $stmt->execute([$cliente, $descripcion]);
            $success = "✅ Reclamo registrado correctamente.";
        } catch (PDOException $e) {
            $error = "❌ Error al registrar: " . $e->getMessage();
        }
    }
}

/* ===========================
   LÓGICA: ACCIONES (GET)
=========================== */
if (isset($_GET['id'], $_GET['accion'])) {
    $id = intval($_GET['id']);
    
    try {
        if ($_GET['accion'] == 'eliminar') {
            $stmt = $conexion->prepare("DELETE FROM reclamos WHERE id_reclamo = ?");
            $stmt->execute([$id]);
            $success = "🗑️ Reclamo eliminado del sistema.";
        } 
        elseif ($_GET['accion'] == 'cambiar_estado' && isset($_GET['estado'])) {
            $nuevoEstado = $_GET['estado'];
            // Validar que el estado sea uno de los permitidos
            if(in_array($nuevoEstado, ['Pendiente', 'En Proceso', 'Atendido'])){
                $stmt = $conexion->prepare("UPDATE reclamos SET estado = ? WHERE id_reclamo = ?");
                $stmt->execute([$nuevoEstado, $id]);
                $success = "🔄 Estado actualizado a: " . $nuevoEstado;
            }
        }
    } catch (Exception $e) {
        $error = "Error al procesar la solicitud.";
    }
}

/* ===========================
   DATOS Y PAGINACIÓN
=========================== */
$busqueda = trim($_GET['q'] ?? '');
$pagina   = max(1, intval($_GET['pagina'] ?? 1));
$limit    = 5;
$offset   = ($pagina - 1) * $limit;

// Filtros
$sqlWhere = "";
$params   = [];
if ($busqueda) {
    $sqlWhere = "WHERE cliente LIKE ? OR descripcion LIKE ?";
    $params   = ["%$busqueda%", "%$busqueda%"];
}

// Obtener datos
$stmt = $conexion->prepare("SELECT * FROM reclamos $sqlWhere ORDER BY CASE WHEN estado = 'Pendiente' THEN 0 ELSE 1 END, fecha DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$reclamos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Paginación y Estadísticas
$totalRows = $conexion->prepare("SELECT COUNT(*) FROM reclamos $sqlWhere");
$totalRows->execute($params);
$totalRegistros = $totalRows->fetchColumn();
$totalPaginas = ceil($totalRegistros / $limit);

// Contadores para el Dashboard
$pendientes = $conexion->query("SELECT COUNT(*) FROM reclamos WHERE estado='Pendiente'")->fetchColumn();
$atendidos  = $conexion->query("SELECT COUNT(*) FROM reclamos WHERE estado='Atendido'")->fetchColumn();
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-danger"><i class="bi bi-journal-x"></i> Libro de Reclamaciones</h2>
            <p class="text-muted mb-0">Gestión de incidencias y quejas de clientes.</p>
        </div>
        <button class="btn btn-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRegistro">
            <i class="bi bi-plus-lg"></i> Nuevo Reclamo
        </button>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-danger text-white h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1" style="opacity: 0.8;">Pendientes de Atención</h6>
                        <h2 class="fw-bold mb-0"><?= $pendientes ?></h2>
                    </div>
                    <div class="display-4"><i class="bi bi-exclamation-triangle"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1" style="opacity: 0.8;">Casos Resueltos</h6>
                        <h2 class="fw-bold mb-0"><?= $atendidos ?></h2>
                    </div>
                    <div class="display-4"><i class="bi bi-check2-circle"></i></div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="bi bi-exclamation-octagon-fill me-2"></i> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow border-0 rounded-3">
        <div class="card-header bg-white py-3">
            <form method="GET" class="d-flex" role="search">
                <input class="form-control me-2" type="search" name="q" placeholder="Buscar por cliente o ID..." value="<?= htmlspecialchars($busqueda) ?>">
                <button class="btn btn-dark" type="submit">Buscar</button>
            </form>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Código</th>
                        <th>Cliente</th>
                        <th>Detalle (Resumen)</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reclamos)): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">No hay reclamos registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($reclamos as $r): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-secondary">#REC-<?= str_pad($r['id_reclamo'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($r['cliente']) ?></td>
                                <td>
                                    <span class="text-muted d-inline-block text-truncate" style="max-width: 250px;">
                                        <?= htmlspecialchars($r['descripcion']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                        $badgeClass = match($r['estado']) {
                                            'Pendiente' => 'bg-danger',
                                            'En Proceso' => 'bg-warning text-dark',
                                            'Atendido' => 'bg-success',
                                            default => 'bg-secondary'
                                        };
                                    ?>
                                    <span class="badge rounded-pill <?= $badgeClass ?>"><?= $r['estado'] ?></span>
                                </td>
                                <td class="text-muted small">
                                    <i class="bi bi-calendar3"></i> <?= date('d/m/Y', strtotime($r['fecha'])) ?>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verDetalle('<?= htmlspecialchars($r['cliente']) ?>', '<?= htmlspecialchars(str_replace(["\r","\n"], " ", $r['descripcion'])) ?>', '<?= $r['fecha'] ?>')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-light border dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="bi bi-gear-fill"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow">
                                            <li><h6 class="dropdown-header">Cambiar Estado</h6></li>
                                            <li><a class="dropdown-item" href="?id=<?= $r['id_reclamo'] ?>&accion=cambiar_estado&estado=En Proceso">🟠 En Proceso</a></li>
                                            <li><a class="dropdown-item" href="?id=<?= $r['id_reclamo'] ?>&accion=cambiar_estado&estado=Atendido">🟢 Atendido (Solucionado)</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="?id=<?= $r['id_reclamo'] ?>&accion=eliminar" onclick="return confirm('¿Eliminar este registro permanentemente?')">🗑️ Eliminar</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPaginas > 1): ?>
        <div class="card-footer bg-white py-3">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $i ?>&q=<?= urlencode($busqueda) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="modalRegistro" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Registrar Nueva Incidencia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="registrar">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Cliente Afectado</label>
                        <input type="text" name="cliente" class="form-control" placeholder="Nombre completo" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción del Problema</label>
                        <textarea name="descripcion" class="form-control" rows="5" placeholder="Describa detalladamente el reclamo..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Registrar Reclamo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Detalle del Reclamo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 class="text-muted small">CLIENTE:</h6>
                <p class="fw-bold fs-5" id="verCliente"></p>
                <hr>
                <h6 class="text-muted small">FECHA:</h6>
                <p id="verFecha"></p>
                <h6 class="text-muted small">DESCRIPCIÓN:</h6>
                <div class="p-3 bg-light rounded border">
                    <p class="mb-0" id="verDescripcion"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    function verDetalle(cliente, desc, fecha) {
        document.getElementById('verCliente').innerText = cliente;
        document.getElementById('verDescripcion').innerText = desc;
        document.getElementById('verFecha').innerText = new Date(fecha).toLocaleDateString() + ' ' + new Date(fecha).toLocaleTimeString();
        var myModal = new bootstrap.Modal(document.getElementById('modalDetalle'));
        myModal.show();
    }
</script>

<?php include 'layout/footer.php'; ?>