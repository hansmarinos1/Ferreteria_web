<?php
/* =========================================================
   CONFIGURACIÓN GENERAL DEL SISTEMA
========================================================= */

// 1. Definir Zona Horaria (Importante para Perú)
date_default_timezone_set('America/Lima');

// 2. Datos de Conexión (Variables para fácil edición)
$host     = 'localhost';
$dbname   = 'ferreteria_comas';
$username = 'root';
$password = ''; // En XAMPP suele ser vacío
$charset  = 'utf8mb4';

/* =========================================================
   CREAR CONEXIÓN PDO
========================================================= */
try {
    // Cadena de conexión (DSN)
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

    // Opciones avanzadas de PDO
    $options = [
        // Lanza excepciones en caso de error (vital para try-catch)
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // Devuelve los datos como array asociativo (['nombre' => 'Martillo'])
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Usa sentencias preparadas nativas (Mayor seguridad)
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Instancia de conexión global
    $conexion = new PDO($dsn, $username, $password, $options);

    // Opcional: Sincronizar nombres de meses en español en MySQL (si tienes permisos)
    $conexion->exec("SET lc_time_names = 'es_PE'");

} catch (PDOException $e) {
    // En caso de error, matamos el proceso y mostramos mensaje
    // En producción, lo ideal es no mostrar $e->getMessage() al usuario final
    die("
        <div style='color: red; padding: 20px; border: 1px solid red; background: #ffeeee; font-family: sans-serif; text-align: center;'>
            <h3>❌ Error Crítico</h3>
            <p>No se pudo conectar a la base de datos.</p>
            <small>Detalle técnico: " . $e->getMessage() . "</small>
        </div>
    ");
}
?>