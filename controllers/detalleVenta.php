<?php
session_start();

// 1. SEGURIDAD: Evitar acceso directo si no hay sesión
if (!isset($_SESSION['usuario'])) {
    echo '<div class="alert alert-danger">Acceso denegado. Su sesión ha expirado.</div>';
    exit;
}

require_once '../config/conexion.php';
require_once '../models/Venta.php';

// 2. VALIDAR ENTRADA
$id_venta = isset($_GET['id_venta']) ? (int) $_GET['id_venta'] : 0;

if ($id_venta <= 0) {
    echo '<div class="alert alert-warning">ID de venta no válido.</div>';
    exit;
}

// 3. OBTENER DATOS (Usando la función del modelo plano)
$detalle = obtenerDetalleVenta($id_venta);

// 4. RESPUESTA (HTML)
if (empty($detalle)) {
    echo '<div class="alert alert-info text-center"><i class="bi bi-info-circle"></i> No se encontraron detalles para esta venta.</div>';
    exit;
}
?>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead class="table-dark text-center">
            <tr>
                <th>Producto</th>
                <th>Cant.</th>
                <th>Precio Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $granTotal = 0;
            foreach ($detalle as $d): 
                // Aseguramos que los números sean float para evitar errores
                $cantidad = floatval($d['cantidad']);
                $precio   = floatval($d['precio']);
                $subtotal = $cantidad * $precio;
                $granTotal += $subtotal;
            ?>
                <tr>
                    <td>
                        <i class="bi bi-box-seam text-secondary me-1"></i> 
                        <?= htmlspecialchars($d['nombre']) ?>
                    </td>
                    <td class="text-center fw-bold"><?= $cantidad ?></td>
                    <td class="text-end">S/ <?= number_format($precio, 2) ?></td>
                    <td class="text-end fw-bold">S/ <?= number_format($subtotal, 2) ?></td>
                </tr>
            <?php endforeach; ?>
            
            <tr class="table-light">
                <td colspan="3" class="text-end text-uppercase fw-bold fs-5">Total a Pagar:</td>
                <td class="text-end fw-bold fs-5 text-success">S/ <?= number_format($granTotal, 2) ?></td>
            </tr>
        </tbody>
    </table>
</div>