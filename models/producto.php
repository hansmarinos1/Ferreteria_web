<?php
require_once __DIR__ . '/../config/conexion.php';

/* =========================================================
   1. LISTAR PRODUCTOS (Para Inventario y POS)
========================================================= */
function listarProductos() {
    global $conexion;
    // Ordenamos por ID DESC para ver los más nuevos primero
    $sql = "SELECT * FROM productos ORDER BY id_producto DESC";
    $stmt = $conexion->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* =========================================================
   2. BUSCAR PRODUCTO POR ID (Para validar ventas)
========================================================= */
function buscarProducto($id_producto) {
    global $conexion;
    $sql = "SELECT * FROM productos WHERE id_producto = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_producto]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/* =========================================================
   3. REGISTRAR NUEVO PRODUCTO
========================================================= */
function registrarProducto($nombre, $precio, $stock) {
    global $conexion;
    try {
        $sql = "INSERT INTO productos (nombre, precio, stock) VALUES (?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([$nombre, $precio, $stock]);
    } catch (PDOException $e) {
        return false; // Error (probablemente nombre duplicado si tienes UNIQUE)
    }
}

/* =========================================================
   4. EDITAR PRODUCTO
========================================================= */
function editarProducto($id, $nombre, $precio, $stock) {
    global $conexion;
    try {
        $sql = "UPDATE productos SET nombre = ?, precio = ?, stock = ? WHERE id_producto = ?";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([$nombre, $precio, $stock, $id]);
    } catch (PDOException $e) {
        return false;
    }
}

/* =========================================================
   5. ELIMINAR PRODUCTO (Protegido)
========================================================= */
function eliminarProducto($id) {
    global $conexion;
    try {
        $sql = "DELETE FROM productos WHERE id_producto = ?";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        // Retorna false si el producto ya está en una venta (Foreign Key)
        return false;
    }
}

/* =========================================================
   6. ACTUALIZAR STOCK (CRÍTICO PARA VENTAS)
   - Esta función se llama desde models/Venta.php
   - Blindaje: SQL verifica que stock >= cantidad antes de restar.
========================================================= */
function actualizarStock($id_producto, $cantidad, $pdo_conexion = null) {
    global $conexion;
    $cn = $pdo_conexion ?: $conexion; // Usa la conexión global o la de la transacción

    // MEJORA DE SEGURIDAD:
    // "AND stock >= ?" asegura que la base de datos rechace la resta 
    // si el stock va a quedar negativo, incluso si PHP falló en validarlo.
    $sql = "UPDATE productos 
            SET stock = stock - ? 
            WHERE id_producto = ? AND stock >= ?";
            
    $stmt = $cn->prepare($sql);
    $exito = $stmt->execute([$cantidad, $id_producto, $cantidad]);
    
    // Verificamos si realmente se actualizó alguna fila
    return $exito && $stmt->rowCount() > 0;
}
?>