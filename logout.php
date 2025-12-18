<?php
// Iniciar sesión para poder manipularla
session_start();

// 1. Limpiar todas las variables de sesión
$_SESSION = [];

// 2. Invalidar la cookie de sesión (Seguridad crítica)
// Se usa time() - 42000 que es un estándar seguro para asegurar que está en el pasado
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000, 
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 3. Destruir la sesión en el servidor
session_destroy();

// 4. MEJORA: Prevenir caché del navegador
// Esto ayuda a que si el usuario da clic en "Atrás", el navegador no muestre
// la página anterior cacheada, sino que obligue a recargar (y pedirá login).
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 5. MEJORA: Redirigir con una variable
// Agregamos '?m=1' para que en el index.php puedas mostrar un mensaje tipo "Sesión cerrada correctamente"
header("Location: index.php?m=1");
exit;
?>