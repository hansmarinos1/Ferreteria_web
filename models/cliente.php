<?php
require_once __DIR__ . '/../config/conexion.php';

/* =========================================================
   LISTAR CLIENTES
========================================================= */
function listarClientes() {
    global $conexion;

    $sql = "SELECT * FROM clientes ORDER BY id_cliente DESC";
    $stmt = $conexion->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* =========================================================
   REGISTRAR CLIENTE (Evita DNI duplicado)
========================================================= */
function registrarCliente($nombre, $dni) {
    global $conexion;

    // 1. Verificar si el DNI ya existe
    $check = $conexion->prepare("SELECT COUNT(*) FROM clientes WHERE dni = ?");
    $check->execute([$dni]);
    
    if ($check->fetchColumn() > 0) {
        return false; // DNI duplicado, no registramos
    }

    // 2. Insertar
    try {
        $sql = "INSERT INTO clientes (nombre, dni, fecha_creacion) VALUES (?, ?, NOW())";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([$nombre, $dni]);
    } catch (PDOException $e) {
        return false;
    }
}

/* =========================================================
   ACTUALIZAR CLIENTE
========================================================= */
function actualizarCliente($id, $nombre, $dni) {
    global $conexion;

    // Nota: Aquí podrías validar también que el nuevo DNI no pertenezca a otro ID
    try {
        $sql = "UPDATE clientes SET nombre = ?, dni = ? WHERE id_cliente = ?";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([$nombre, $dni, $id]);
    } catch (PDOException $e) {
        return false;
    }
}

/* =========================================================
   ELIMINAR CLIENTE
========================================================= */
function eliminarCliente($id) {
    global $conexion;

    try {
        $sql = "DELETE FROM clientes WHERE id_cliente = ?";
        $stmt = $conexion->prepare($sql);
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        // Si el cliente tiene ventas asociadas (Foreign Key), entrará aquí.
        // Retornamos false para indicar que no se pudo borrar.
        return false;
    }
}

/* =========================================================
   BUSCAR CLIENTE POR ID
========================================================= */
function buscarCliente($id) {
    global $conexion;

    $sql = "SELECT * FROM clientes WHERE id_cliente = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>