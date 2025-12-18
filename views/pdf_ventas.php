<?php
session_start();
date_default_timezone_set('America/Lima'); // Ajusta a tu zona horaria

/* ==========================================
   1. SEGURIDAD Y DEPENDENCIAS
========================================== */
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit;
}

require_once '../libreria/ReportePDF.php';
require_once '../models/Venta.php';

/* ==========================================
   2. OBTENCIÓN Y PROCESAMIENTO DE DATOS
========================================== */
// Filtro opcional: ID de venta específica
$id_venta = filter_input(INPUT_GET, 'id_venta', FILTER_VALIDATE_INT);

// Obtener listado (Aquí podrías agregar filtros de fechas en el futuro)
$ventas = Venta::listar();

// CÁLCULOS DE RESUMEN (KPIs)
$total_ingresos = 0;
$total_transacciones = count($ventas);

foreach ($ventas as $v) {
    // Asegurarse que el total sea numérico
    $total_ingresos += floatval($v['total']);
}

/* ==========================================
   3. GENERACIÓN DEL PDF
========================================== */
$pdf = new ReportePDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

// --- TÍTULO DEL REPORTE ---
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('REPORTE DE VENTAS'), 0, 1, 'C');
$pdf->Ln(2);

// --- BLOQUE DE INFORMACIÓN (Resumen) ---
$pdf->SetFillColor(245, 245, 245); // Color gris claro
$pdf->SetFont('Arial', '', 10);

// Dibujamos un recuadro de información
$pdf->Rect($pdf->GetX(), $pdf->GetY(), 190, 25, 'F'); // Fondo gris
$pdf->SetXY($pdf->GetX() + 5, $pdf->GetY() + 5);

// Columna 1: Datos de Generación
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(35, 6, utf8_decode('Generado por:'), 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(60, 6, utf8_decode($_SESSION['usuario']), 0, 0);

// Columna 2: Totales Monetarios
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(35, 6, utf8_decode('Total Ingresos:'), 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'S/ ' . number_format($total_ingresos, 2), 0, 1);

// Segunda Fila
$pdf->SetX($pdf->GetX() + 5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(35, 6, utf8_decode('Fecha Emisión:'), 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(60, 6, date('d/m/Y H:i:s A'), 0, 0);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(35, 6, utf8_decode('Transacciones:'), 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, $total_transacciones . ' ventas registradas', 0, 1);

$pdf->Ln(15); // Espacio antes de la tabla

/* ==========================================
   4. LISTADO DE VENTAS (TABLA)
========================================== */
// Usamos tu método existente en la librería para dibujar la tabla
if (!empty($ventas)) {
    $pdf->ventas($ventas); 
} else {
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell(0, 10, utf8_decode('No hay registros de ventas para mostrar.'), 0, 1, 'C');
}

/* ==========================================
   5. DETALLE DE UNA VENTA (Si se solicitó)
========================================== */
if ($id_venta) {
    $detalle = Venta::detalle($id_venta);

    // Salto de página para el detalle
    $pdf->AddPage();
    
    // Título de la sección
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(33, 37, 41); // Color oscuro
    $pdf->Cell(0, 10, utf8_decode("Detalle de la Venta #") . str_pad($id_venta, 5, "0", STR_PAD_LEFT), 0, 1, 'L');
    $pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX() + 190, $pdf->GetY()); // Línea separadora
    $pdf->Ln(5);

    if (!empty($detalle)) {
        // Usamos tu método existente para el detalle
        $pdf->detalleVenta($detalle);
        
        // Agregar un pie de página al detalle
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'I', 9);
        $pdf->Cell(0, 10, utf8_decode('* Precios incluyen IGV. Documento interno de control.'), 0, 1, 'L');
    } else {
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(220, 53, 69); // Rojo
        $pdf->Cell(0, 10, utf8_decode('Error: No se encontraron detalles para esta venta.'), 0, 1, 'L');
        $pdf->SetTextColor(0, 0, 0);
    }
}

/* ==========================================
   6. SALIDA
========================================== */
$pdf->Output('I', 'Reporte_Ventas_' . date('Ymd_His') . '.pdf');
exit;
?>