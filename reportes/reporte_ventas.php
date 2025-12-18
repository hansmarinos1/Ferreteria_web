<?php
session_start();
date_default_timezone_set('America/Lima');

/* ==========================================
   1. SEGURIDAD
========================================== */
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit;
}

// IMPORTANTE: Cargamos el modelo de funciones
require_once '../config/conexion.php';
require_once '../models/Venta.php'; 
require_once '../libreria/ReportePDF.php';

/* ==========================================
   2. FILTROS Y DATOS
========================================== */
$fecha_inicio = $_GET['inicio'] ?? date('Y-m-01');
$fecha_fin    = $_GET['fin'] ?? date('Y-m-d');
$id_venta     = isset($_GET['id_venta']) ? intval($_GET['id_venta']) : 0;

// OBTENER DATOS USANDO LA FUNCIÓN PLANA (CORRECCIÓN AQUÍ)
// En lugar de Venta::listar(), usamos listarVentas()
$todasVentas = listarVentas(); 

// Filtrar por fecha manualmente en PHP
$ventasFiltradas = [];
$totalIngresos = 0;

foreach ($todasVentas as $v) {
    // Extraer solo la fecha YYYY-MM-DD
    $fechaVenta = date('Y-m-d', strtotime($v['fecha']));
    
    if ($fechaVenta >= $fecha_inicio && $fechaVenta <= $fecha_fin) {
        $ventasFiltradas[] = $v;
        $totalIngresos += floatval($v['total']);
    }
}

/* ==========================================
   3. GENERAR PDF
========================================== */
$pdf = new ReportePDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

// Título
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('REPORTE DE VENTAS'), 0, 1, 'C');

// Subtítulo con fechas
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 5, utf8_decode("Del: " . date('d/m/Y', strtotime($fecha_inicio)) . "  Al: " . date('d/m/Y', strtotime($fecha_fin))), 0, 1, 'C');
$pdf->Ln(5);

// Resumen Monetario
$pdf->SetFillColor(240, 240, 240);
$pdf->Rect($pdf->GetX(), $pdf->GetY(), 190, 15, 'F');
$pdf->SetXY($pdf->GetX() + 5, $pdf->GetY() + 4);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(30, 7, 'Total Ingresos:', 0, 0);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 7, 'S/ ' . number_format($totalIngresos, 2), 0, 0);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(30, 7, 'Transacciones:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(30, 7, count($ventasFiltradas), 0, 1);
$pdf->Ln(10);

// Tabla de Ventas
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 7, 'ID', 1, 0, 'C');
$pdf->Cell(80, 7, 'Cliente', 1, 0, 'L');
$pdf->Cell(45, 7, 'Fecha', 1, 0, 'C');
$pdf->Cell(45, 7, 'Total', 1, 1, 'C');

$pdf->SetFont('Arial', '', 9);

if (count($ventasFiltradas) > 0) {
    foreach ($ventasFiltradas as $row) {
        $pdf->Cell(20, 7, $row['id_venta'], 1, 0, 'C');
        $pdf->Cell(80, 7, utf8_decode($row['cliente']), 1, 0, 'L');
        $pdf->Cell(45, 7, date('d/m/Y H:i', strtotime($row['fecha'])), 1, 0, 'C');
        $pdf->Cell(45, 7, 'S/ ' . number_format($row['total'], 2), 1, 1, 'R');
    }
} else {
    $pdf->Cell(190, 10, 'No hay ventas en este rango de fechas.', 1, 1, 'C');
}

/* ==========================================
   4. DETALLE DE VENTA INDIVIDUAL (Si se pide)
========================================== */
if ($id_venta > 0) {
    // CORRECCIÓN AQUÍ: Usamos obtenerDetalleVenta() en lugar de Venta::detalle()
    $detalle = obtenerDetalleVenta($id_venta);

    if ($detalle) {
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, utf8_decode("DETALLE VENTA #$id_venta"), 0, 1, 'L');
        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(100, 7, 'Producto', 1);
        $pdf->Cell(30, 7, 'Cant.', 1, 0, 'C');
        $pdf->Cell(30, 7, 'P. Unit', 1, 0, 'R');
        $pdf->Cell(30, 7, 'Subtotal', 1, 1, 'R');

        $pdf->SetFont('Arial', '', 10);
        foreach ($detalle as $d) {
            $sub = $d['cantidad'] * $d['precio'];
            $pdf->Cell(100, 7, utf8_decode($d['nombre']), 1);
            $pdf->Cell(30, 7, $d['cantidad'], 1, 0, 'C');
            $pdf->Cell(30, 7, number_format($d['precio'], 2), 1, 0, 'R');
            $pdf->Cell(30, 7, number_format($sub, 2), 1, 1, 'R');
        }
    }
}

$pdf->Output('I', 'Reporte_Ventas.pdf');
exit;
?>