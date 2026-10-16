<?php

// Módulo de conexión al SGBD (MySQL / MariaDB) mediante PDO.

declare(strict_types=1);

// Devuelve una variable de entorno o el valor por defecto indicado

function parametroBD(string $nombre, string $porDefecto): string
{
    $valor = getenv($nombre);

    return ($valor !== false && $valor !== '') ? $valor : $porDefecto;
}

define('DB_HOST', parametroBD('DB_HOST', '127.0.0.1'));
define('DB_PORT', parametroBD('DB_PORT', '3306'));
define('DB_NAME', parametroBD('DB_NAME', 'biblioteca'));
define('DB_USER', parametroBD('DB_USER', 'root'));
define('DB_PASS', parametroBD('DB_PASS', ''));      // XAMPP: root sin contraseña
define('DB_CHARSET', parametroBD('DB_CHARSET', 'utf8mb4'));

const MENSAJE_ERROR_CONEXION =
    'No se pudo establecer conexión con el servidor de datos. '
    . 'Por favor, reintente más tarde o contacte al administrador.';

/* Excepción de dominio para diferenciar los errores de CONEXIÓN
 de los errores de CONSULTA (PDOException) */

class ConexionBDException extends RuntimeException
{
}

// Devuelve una conexión PDO reutilizable durante todo el request.

function obtenerConexion(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        error_log('[BD] Error de conexión: ' . $e->getMessage());
        throw new ConexionBDException(MENSAJE_ERROR_CONEXION, 0, $e);
    }

    return $pdo;
}

/* Atajo usado por las páginas: obtiene la conexión o, si falla,
 muestra la pantalla de error y detiene la ejecución */

function conectarBD(): PDO
{
    try {
        return obtenerConexion();
    } catch (ConexionBDException $e) {
        mostrarErrorConexion();
    }

    /* mostrarErrorConexion() termina con exit(); esta línea es inalcanzable,
    pero mantiene conforme al analizador estático */
    
    exit;
}

function mostrarErrorConexion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!headers_sent()) {
        http_response_code(503);
    }

    $rutaBase = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? ''))), '/');
    $css = $rutaBase . '/assets/css/style.css';
    $raiz = dirname(__DIR__);
    $versionCss = is_file($raiz . '/assets/css/style.css') ? filemtime($raiz . '/assets/css/style.css') : time();
    ?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Biblioteca - Error de conexión</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($css, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo $versionCss; ?>">
</head>
<body class="auth-page">
<?php include $raiz . '/vistas/layouts/header.php'; ?>
<main>
  <div class="resultado">
    <div class="ficha-resultado ficha-resultado--error">
      <span class="sello sello--error">Sin conexión</span>
      <p class="error-conexion__texto"><?php echo htmlspecialchars(MENSAJE_ERROR_CONEXION, ENT_QUOTES, 'UTF-8'); ?></p>
      <p class="error-conexion__codigo">Ref. HTTP 503 · Servicio de datos no disponible</p>
      <a href="index.php?c=auth&amp;a=login" class="panel-premium__volver">Reintentar inicio de sesión</a>
    </div>
  </div>
</main>
<?php include $raiz . '/vistas/layouts/footer.php'; ?>
</body>
</html>
    <?php
    exit;
}