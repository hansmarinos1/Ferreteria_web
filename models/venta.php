<?php
require_once __DIR__ . '/../config/conexion.php';

/* =========================================================
   1. LISTAR VENTAS (Función Plana)
========================================================= */
function listarVentas() {
    global $conexion;

    $sql = "
        SELECT v.id_venta, 
               c.nombre AS cliente, 
               v.fecha, 
               v.total
        FROM ventas v
        INNER JOIN clientes c ON c.id_cliente = v.id_cliente
        ORDER BY v.id_venta DESC
    ";

    $stmt = $conexion->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* =========================================================
   2. REGISTRAR VENTA (Función Plana con Transacción)
========================================================= */
function registrarVenta($id_cliente, $productos) {
    global $conexion;

    try {
        // Iniciamos transacción para que se guarde todo o nada
        $conexion->beginTransaction();

        $total = 0;

        // A. Calcular total y validar stock real
        foreach ($productos as $p) {
            // Consultamos precio y stock de la BD por seguridad
            $stmt = $conexion->prepare("SELECT stock, precio FROM productos WHERE id_producto = ?");
            $stmt->execute([$p['id_producto']]);
            $prodDB = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$prodDB) {
                throw new Exception("Producto no encontrado.");
            }
            if ($p['cantidad'] > $prodDB['stock']) {
                throw new Exception("Stock insuficiente.");
            }

            // Usamos el precio de la BD
            $total += $p['cantidad'] * $prodDB['precio'];
        }

        // B. Insertar Venta (Cabecera)
        $stmt = $conexion->prepare("INSERT INTO ventas (id_cliente, fecha, total) VALUES (?, NOW(), ?)");
        $stmt->execute([$id_cliente, $total]);
        $id_venta = $conexion->lastInsertId();

        // C. Insertar Detalle y Restar Stock
        $stmtDetalle = $conexion->prepare("INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio) VALUES (?, ?, ?, ?)");
        $stmtUpdate  = $conexion->prepare("UPDATE productos SET stock = stock - ? WHERE id_producto = ?");

        foreach ($productos as $p) {
            // Obtenemos precio real de nuevo (o podrías guardarlo en un array temporal arriba)
            $stmtPrecio = $conexion->prepare("SELECT precio FROM productos WHERE id_producto = ?");
            $stmtPrecio->execute([$p['id_producto']]);
            $precioReal = $stmtPrecio->fetchColumn();

            // Guardar detalle
            $stmtDetalle->execute([$id_venta, $p['id_producto'], $p['cantidad'], $precioReal]);

            // Restar stock
            $stmtUpdate->execute([$p['cantidad'], $p['id_producto']]);
        }

        // Si todo salió bien, confirmamos
        $conexion->commit();
        return $id_venta;

    } catch (Exception $e) {
        // Si algo falló, deshacemos todo
        $conexion->rollBack();
        return false;
    }
}

/* =========================================================
   3. OBTENER DETALLE (Para el Modal o Reportes)
========================================================= */
function obtenerDetalleVenta($id_venta) {
    global $conexion;

    $sql = "
        SELECT p.nombre, 
               d.cantidad, 
               d.precio 
        FROM detalle_venta d 
        INNER JOIN productos p ON d.id_producto = p.id_producto 
        WHERE d.id_venta = ?
    ";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_venta]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>