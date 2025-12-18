<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit;
}

require_once '../config/conexion.php';
require_once '../libreria/fpdf/fpdf.php';

/* =========================================================
   1. OBTENER DATOS (Hacemos las consultas PRIMERO)
   Así los datos sirven tanto para el PDF como para la vista HTML.
========================================================= */

// A. Totales por Cliente (Top 10)
$sqlClientes = "
    SELECT c.nombre, SUM(v.total) as total 
    FROM ventas v
    INNER JOIN clientes c ON v.id_cliente = c.id_cliente
    GROUP BY c.id_cliente 
    ORDER BY total DESC 
    LIMIT 10";
$stmt = $conexion->query($sqlClientes);
$totalesClientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// B. Totales por Producto (Top 10)
$sqlProductos = "
    SELECT p.nombre, SUM(d.cantidad * d.precio) as total_vendido
    FROM detalle_venta d
    INNER JOIN productos p ON d.id_producto = p.id_producto
    GROUP BY p.id_producto
    ORDER BY total_vendido DESC
    LIMIT 10";
$stmt = $conexion->query($sqlProductos);
$totalesProductos = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================================================
   2. EXPORTAR PDF (ESTO DEBE IR ANTES DEL HEADER HTML)
========================================================= */
if (isset($_GET['exportar']) && $_GET['exportar'] === 'pdf') {
    
    // Limpiamos cualquier basura del buffer de salida
    if (ob_get_length()) ob_end_clean(); 
    
    class PDF_Resumen extends FPDF {
        function Header() {
            $this->SetFont('Arial','B',14);
            $this->Cell(0,10,utf8_decode('REPORTE DE RENDIMIENTO - FERRETERÍA COMAS'),0,1,'C');
            $this->SetFont('Arial','',10);
            $this->Cell(0,5,'Fecha de emision: '.date('d/m/Y H:i'),0,1,'C');
            $this->Ln(10);
        }
        function Footer() {
            $this->SetY(-15);
            $this->SetFont('Arial','I',8);
            $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
        }
        function tabla($titulo, $header, $data) {
            $this->SetFont('Arial','B',12);
            $this->Cell(0,10,utf8_decode($titulo),0,1,'L');
            $this->SetFillColor(230,230,230);
            
            $this->SetFont('Arial','B',10);
            $this->Cell(140,8,$header[0],1,0,'L',true);
            $this->Cell(50,8,$header[1],1,1,'R',true);
            
            $this->SetFont('Arial','',10);
            foreach($data as $row) {
                // Ajuste para leer 'total' o 'total_vendido'
                $monto = isset($row['total']) ? $row['total'] : $row['total_vendido'];
                $this->Cell(140,7,utf8_decode($row['nombre']),1);
                $this->Cell(50,7,'S/ '.number_format($monto,2),1,1,'R');
            }
            $this->Ln(5);
        }
    }

    $pdf = new PDF_Resumen();
    $pdf->AliasNbPages();
    $pdf->AddPage();

    $pdf->tabla('Top Clientes', ['Cliente', 'Total Comprado'], $totalesClientes);
    $pdf->tabla('Productos Más Vendidos', ['Producto', 'Total Vendido'], $totalesProductos);

    $pdf->Output('I','Resumen_Ventas.pdf');
    exit; // ¡IMPORTANTE! Aquí se detiene el script para no imprimir el HTML de abajo
}

/* =========================================================
   3. VISTA HTML (SOLO SE EJECUTA SI NO ES PDF)
========================================================= */
include 'layout/header.php'; // <--- El header va AQUÍ, después del bloque PDF
?>

<div class="container mt-4 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary"><i class="bi bi-pie-chart-fill"></i> Estadísticas de Negocio</h2>
        <a href="?exportar=pdf" target="_blank" class="btn btn-danger">
            <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
        </a>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-6">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0 text-dark">🏆 Top Clientes</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartClientes" style="max-height: 250px;"></canvas>
                    
                    <div class="table-responsive mt-4">
                        <table class="table table-sm table-striped">
                            <thead class="table-dark">
                                <tr><th>Cliente</th><th class="text-end">Total (S/)</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($totalesClientes as $c): ?>
                                <tr>
                                    <td><?= htmlspecialchars($c['nombre']) ?></td>
                                    <td class="text-end fw-bold">S/ <?= number_format($c['total'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0 text-warning">📦 Productos Estrella</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartProductos" style="max-height: 250px;"></canvas>

                    <div class="table-responsive mt-4">
                        <table class="table table-sm table-striped">
                            <thead class="table-warning">
                                <tr><th>Producto</th><th class="text-end">Venta Total (S/)</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($totalesProductos as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                                    <td class="text-end fw-bold">S/ <?= number_format($p['total_vendido'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // 1. Configuración Gráfico Clientes (Pastel)
    const ctxC = document.getElementById('chartClientes');
    new Chart(ctxC, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($totalesClientes, 'nombre')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($totalesClientes, 'total')) ?>,
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796']
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'right' } } }
    });

    // 2. Configuración Gráfico Productos (Barras)
    const ctxP = document.getElementById('chartProductos');
    new Chart(ctxP, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($totalesProductos, 'nombre')) ?>,
            datasets: [{
                label: 'Ventas en Soles',
                data: <?= json_encode(array_column($totalesProductos, 'total_vendido')) ?>,
                backgroundColor: '#f6c23e',
                borderRadius: 4
            }]
        },
        options: { 
            responsive: true, 
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
</script>

<?php include 'layout/footer.php'; ?>