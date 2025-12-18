<?php
session_start();

/* ===========================
   SEGURIDAD
=========================== */
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit;
}

require '../config/conexion.php'; // Asegúrate que la ruta sea correcta
include 'layout/header.php';

$success = "";
$error   = "";

/* ===========================
   LÓGICA: REGISTRAR (POST)
=========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'registrar') {
    $nombre  = trim($_POST['nombre']);
    $mensaje = trim($_POST['mensaje']);

    if (empty($nombre) || empty($mensaje)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        try {
            $stmt = $conexion->prepare("INSERT INTO sugerencias (nombre, mensaje, fecha, estado) VALUES (?, ?, NOW(), 'Pendiente')");
            $stmt->execute([$nombre, $mensaje]);
            $success = "✅ Sugerencia registrada correctamente.";
        } catch (PDOException $e) {
            $error = "❌ Error en BD: " . $e->getMessage();
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
            $stmt = $conexion->prepare("DELETE FROM sugerencias WHERE id_sugerencia = ?");
            $stmt->execute([$id]);
            $success = "🗑️ Registro eliminado.";
        } elseif ($_GET['accion'] == 'cambiar_estado' && isset($_GET['estado'])) {
            $nuevoEstado = $_GET['estado'];
            $stmt = $conexion->prepare("UPDATE sugerencias SET estado = ? WHERE id_sugerencia = ?");
            $stmt->execute([$nuevoEstado, $id]);
            $success = "🔄 Estado actualizado a: $nuevoEstado";
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
$limit    = 6;
$offset   = ($pagina - 1) * $limit;

// Filtros SQL
$sqlWhere = "";
$params   = [];
if ($busqueda) {
    $sqlWhere = "WHERE nombre LIKE ? OR mensaje LIKE ?";
    $params   = ["%$busqueda%", "%$busqueda%"];
}

// Obtener datos
$stmt = $conexion->prepare("SELECT * FROM sugerencias $sqlWhere ORDER BY fecha DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$sugerencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total para paginación
$stmtTotal = $conexion->prepare("SELECT COUNT(*) FROM sugerencias $sqlWhere");
$stmtTotal->execute($params);
$totalRows = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRows / $limit);

// Conteos rápidos para las tarjetas
$totalPendientes = $conexion->query("SELECT COUNT(*) FROM sugerencias WHERE estado='Pendiente'")->fetchColumn();
$totalActivos    = $conexion->query("SELECT COUNT(*) FROM sugerencias WHERE estado='Activo'")->fetchColumn();
?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="bi bi-chat-square-quote"></i> Gestión de Sugerencias</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNueva">
            <i class="bi bi-plus-lg"></i> Nueva Sugerencia
        </button>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body d-flex align-items-center">
                    <div class="display-6 text-warning me-3"><i class="bi bi-hourglass-split"></i></div>
                    <div>
                        <h5 class="card-title mb-0">Pendientes</h5>
                        <p class="card-text fs-4 fw-bold text-dark"><?= $totalPendientes ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body d-flex align-items-center">
                    <div class="display-6 text-success me-3"><i class="bi bi-check-circle"></i></div>
                    <div>
                        <h5 class="card-title mb-0">Activas</h5>
                        <p class="card-text fs-4 fw-bold text-dark"><?= $totalActivos ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body d-flex align-items-center">
                    <div class="display-6 text-primary me-3"><i class="bi bi-files"></i></div>
                    <div>
                        <h5 class="card-title mb-0">Total Registros</h5>
                        <p class="card-text fs-4 fw-bold text-dark"><?= $totalRows ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <form method="GET" class="d-flex" role="search">
                <input class="form-control me-2" type="search" name="q" placeholder="Buscar por nombre o contenido..." value="<?= htmlspecialchars($busqueda) ?>">
                <button class="btn btn-outline-dark" type="submit">Buscar</button>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Fecha</th>
                            <th>Usuario</th>
                            <th>Mensaje (Extracto)</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sugerencias)): ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted">No se encontraron resultados</td></tr>
                        <?php else: ?>
                            <?php foreach ($sugerencias as $s): ?>
                                <tr>
                                    <td class="ps-4 text-nowrap"><?= date('d/m/Y', strtotime($s['fecha'])) ?> <br> <small class="text-muted"><?= date('H:i', strtotime($s['fecha'])) ?></small></td>
                                    <td class="fw-bold"><?= htmlspecialchars($s['nombre']) ?></td>
                                    <td style="max-width: 300px;">
                                        <div class="text-truncate text-muted">
                                            <?= htmlspecialchars($s['mensaje']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                            $badge = match($s['estado']) {
                                                'Pendiente' => 'bg-warning text-dark',
                                                'Activo' => 'bg-success',
                                                'Cancelado' => 'bg-secondary',
                                                default => 'bg-light text-dark'
                                            };
                                        ?>
                                        <span class="badge rounded-pill <?= $badge ?>"><?= $s['estado'] ?></span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-primary me-1" 
                                                onclick="verMensaje('<?= htmlspecialchars($s['nombre']) ?>', '<?= htmlspecialchars(str_replace(array("\r", "\n"), ' ', $s['mensaje'])) ?>')">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-light border dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="bi bi-gear"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                                <li><h6 class="dropdown-header">Cambiar Estado</h6></li>
                                                <li><a class="dropdown-item" href="?id=<?= $s['id_sugerencia'] ?>&accion=cambiar_estado&estado=Pendiente"><i class="bi bi-hourglass-split text-warning"></i> Pendiente</a></li>
                                                <li><a class="dropdown-item" href="?id=<?= $s['id_sugerencia'] ?>&accion=cambiar_estado&estado=Activo"><i class="bi bi-check-circle text-success"></i> Activo</a></li>
                                                <li><a class="dropdown-item" href="?id=<?= $s['id_sugerencia'] ?>&accion=cambiar_estado&estado=Cancelado"><i class="bi bi-x-circle text-secondary"></i> Cancelado</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="?id=<?= $s['id_sugerencia'] ?>&accion=eliminar" onclick="return confirm('¿Seguro que deseas eliminar esto?')"><i class="bi bi-trash"></i> Eliminar</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php if ($totalPaginas > 1): ?>
        <div class="card-footer bg-white d-flex justify-content-center py-3">
            <nav>
                <ul class="pagination mb-0">
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

<div class="modal fade" id="modalNueva" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nueva Sugerencia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="registrar">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Cliente / Usuario</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Detalle de la sugerencia</label>
                        <textarea name="mensaje" class="form-control" rows="4" required placeholder="Escriba aquí..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVer" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalVerTitulo"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="p-3 bg-light rounded border">
                    <p class="mb-0 text-break" id="modalVerCuerpo"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Script simple para pasar datos al modal de "Ver"
    function verMensaje(nombre, mensaje) {
        document.getElementById('modalVerTitulo').innerText = 'Sugerencia de: ' + nombre;
        document.getElementById('modalVerCuerpo').innerText = mensaje;
        var myModal = new bootstrap.Modal(document.getElementById('modalVer'));
        myModal.show();
    }
</script>

<?php include 'layout/footer.php'; ?>