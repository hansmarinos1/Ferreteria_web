<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit;
}

require '../config/conexion.php';
include 'layout/header.php';

/* =================================================
   1. CONSULTAS DE DATOS (KPIs y TABLAS)
================================================= */
try {
    // A. Totales Generales
    $totalProductos  = $conexion->query("SELECT COUNT(*) FROM productos")->fetchColumn();
    $totalVentas     = $conexion->query("SELECT COUNT(*) FROM ventas")->fetchColumn();
    $ingresosTotales = $conexion->query("SELECT SUM(total) FROM ventas")->fetchColumn(); // Dinero total
    $totalReclamos   = $conexion->query("SELECT COUNT(*) FROM reclamos WHERE estado = 'Pendiente'")->fetchColumn();

    // B. Alerta de Stock Bajo (Menos de 10 unidades)
    $stmtStock = $conexion->query("SELECT nombre, stock FROM productos WHERE stock <= 10 ORDER BY stock ASC LIMIT 5");
    $stockBajo = $stmtStock->fetchAll(PDO::FETCH_ASSOC);

    // C. Últimas 5 Ventas Recientes
    // Nota: Usamos JOIN para traer el nombre del cliente si tienes la relación hecha, 
    // si no tienes FK, ajusta la consulta según tu tabla.
    $sqlVentas = "
        SELECT v.id_venta, c.nombre as cliente, v.fecha, v.total 
        FROM ventas v 
        INNER JOIN clientes c ON v.id_cliente = c.id_cliente 
        ORDER BY v.fecha DESC LIMIT 5
    ";
    $ultimasVentas = $conexion->query($sqlVentas)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    exit;
}
?>

<div class="container py-4">

    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold text-dark">Hola, <?= htmlspecialchars($_SESSION['usuario']) ?> 👋</h2>
            <p class="text-muted">Aquí tienes el resumen de actividad de la ferretería hoy.</p>
        </div>
        <div class="col-md-4 text-end">
            <span class="badge bg-light text-dark border px-3 py-2">
                <i class="bi bi-calendar-event"></i> <?= date('d/m/Y') ?>
            </span>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-body position-relative">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-shape bg-success bg-opacity-10 text-success rounded-3 p-2 me-3">
                            <i class="bi bi-currency-dollar fs-4"></i>
                        </div>
                        <h6 class="text-muted text-uppercase mb-0 ls-1">Ingresos</h6>
                    </div>
                    <h3 class="fw-bold mb-0">S/ <?= number_format($ingresosTotales ?? 0, 2) ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-shape bg-primary bg-opacity-10 text-primary rounded-3 p-2 me-3">
                            <i class="bi bi-cart-check fs-4"></i>
                        </div>
                        <h6 class="text-muted text-uppercase mb-0 ls-1">Ventas</h6>
                    </div>
                    <h3 class="fw-bold mb-0"><?= $totalVentas ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-shape bg-info bg-opacity-10 text-info rounded-3 p-2 me-3">
                            <i class="bi bi-box-seam fs-4"></i>
                        </div>
                        <h6 class="text-muted text-uppercase mb-0 ls-1">Productos</h6>
                    </div>
                    <h3 class="fw-bold mb-0"><?= $totalProductos ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-shape bg-danger bg-opacity-10 text-danger rounded-3 p-2 me-3">
                            <i class="bi bi-exclamation-circle fs-4"></i>
                        </div>
                        <h6 class="text-muted text-uppercase mb-0 ls-1">Reclamos (Pend.)</h6>
                    </div>
                    <h3 class="fw-bold mb-0"><?= $totalReclamos ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold"><i class="bi bi-clock-history"></i> Ventas Recientes</h6>
                    <a href="ventas.php" class="btn btn-sm btn-outline-primary">Ver todas</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th class="text-end">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($ultimasVentas)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">No hay movimientos recientes</td></tr>
                            <?php else: ?>
                                <?php foreach($ultimasVentas as $v): ?>
                                <tr>
                                    <td>#<?= $v['id_venta'] ?></td>
                                    <td><?= htmlspecialchars($v['cliente']) ?></td>
                                    <td class="small text-muted"><?= date('d/m H:i', strtotime($v['fecha'])) ?></td>
                                    <td class="text-end fw-bold text-success">S/ <?= number_format($v['total'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <h6 class="m-0 fw-bold text-danger"><i class="bi bi-lightning-charge-fill"></i> Stock Crítico</h6>
                </div>
                <div class="list-group list-group-flush">
                    <?php if(empty($stockBajo)): ?>
                        <div class="list-group-item text-center text-muted py-3">Todo el inventario está OK ✅</div>
                    <?php else: ?>
                        <?php foreach($stockBajo as $prod): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-4">
                                <span><?= htmlspecialchars($prod['nombre']) ?></span>
                                <span class="badge bg-danger rounded-pill"><?= $prod['stock'] ?> u.</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if(!empty($stockBajo)): ?>
                    <div class="card-footer bg-white text-center">
                        <a href="productos.php" class="small text-decoration-none">Gestionar Inventario</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card border-0 shadow-sm bg-dark text-white">
                <div class="card-body">
                    <h6 class="mb-3">Accesos Rápidos</h6>
                    <div class="d-grid gap-2">
                        <a href="ventas.php" class="btn btn-primary"><i class="bi bi-cart-plus"></i> Nueva Venta</a>
                        <a href="productos.php" class="btn btn-outline-light"><i class="bi bi-box-seam"></i> Inventario</a>
                        <a href="../reportes/reporte_ventas.php" target="_blank" class="btn btn-outline-light"><i class="bi bi-file-earmark-pdf"></i> Reporte PDF</a>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<style>
    /* Pequeños ajustes visuales */
    .ls-1 { letter-spacing: 1px; font-size: 0.75rem; }
    .icon-shape { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; }
</style>

<?php include 'layout/footer.php'; ?>