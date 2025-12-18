<?php
require_once __DIR__ . '/../config/conexion.php';

/* ==========================
   LOGIN USUARIO
========================== */
function loginUsuario($usuario, $password) {
    global $conexion;

    $sql = "SELECT * FROM usuarios WHERE usuario = ? AND password = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$usuario, $password]);
    return $stmt->fetch(); // Devuelve el usuario o false
}

/* ==========================
   LISTAR USUARIOS
========================== */
function listarUsuarios() {
    global $conexion;

    $sql = "SELECT * FROM usuarios ORDER BY id_usuario DESC";
    $stmt = $conexion->query($sql);
    return $stmt->fetchAll();
}

/* ==========================
   REGISTRAR USUARIO
========================== */
function registrarUsuario($usuario, $password, $rol) {
    global $conexion;

    $sql = "INSERT INTO usuarios (usuario, password, id_rol) VALUES (?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([$usuario, $password, $rol]);
}

/* ==========================
   ACTUALIZAR USUARIO
========================== */
function actualizarUsuario($id, $usuario, $rol, $estado) {
    global $conexion;

    $sql = "UPDATE usuarios SET usuario = ?, id_rol = ?, estado = ? WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([$usuario, $rol, $estado, $id]);
}

/* ==========================
   CAMBIAR PASSWORD
========================== */
function cambiarPassword($id, $password) {
    global $conexion;

    $sql = "UPDATE usuarios SET password = ? WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([$password, $id]);
}

/* ==========================
   ELIMINAR USUARIO
========================== */
function eliminarUsuario($id) {
    global $conexion;

    $sql = "DELETE FROM usuarios WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    return $stmt->execute([$id]);
}
