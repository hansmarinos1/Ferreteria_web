<?php
session_start();

require_once '../config/conexion.php';
require_once '../models/usuario.php';

// 1. Validar que vengan los datos
if (empty($_POST['usuario']) || empty($_POST['password'])) {
    $_SESSION['error_login'] = "Complete todos los campos";
    header("Location: ../index.php");
    exit;
}

$usuario  = trim($_POST['usuario']);
$password = trim($_POST['password']);

// 2. Llamar a la función con AMBOS datos
// La función loginUsuario ahora se encarga de verificar si la contraseña es correcta
$user = loginUsuario($usuario, $password);

// 3. Verificar resultado
if ($user) {
    // Si $user tiene datos, es que el login fue exitoso y seguro
    $_SESSION['usuario'] = $user['usuario'];
    $_SESSION['id']      = $user['id_usuario'];
    $_SESSION['rol']     = $user['id_rol'];

    header("Location: ../views/dashboard.php");
    exit;

} else {
    // Si devuelve false, es error de usuario, contraseña o inactivo
    $_SESSION['error_login'] = "Usuario o contraseña incorrectos.";
    header("Location: ../index.php");
    exit;
}
?>