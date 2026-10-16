<?php
 /* Utilidades de sesión, control de acceso, CSRF y mensajes flash
 Separa la "seguridad de sesión" de la lógica de negocio */

declare(strict_types=1);

function iniciarSesion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function usuarioLogueado(): bool
{
    iniciarSesion();

    return !empty($_SESSION['logueado']) && $_SESSION['logueado'] === true;
}

// Exige sesión activa; si no hay, redirige al login y detiene la ejecución
function requerirLogin(): void
{
    if (!usuarioLogueado()) {
        header('Location: index.php?c=auth&a=login');
        exit;
    }

    try {
        $usuario = (new Usuario(obtenerConexion()))->buscarCuentaPorId(
            (int) ($_SESSION['usuario_id'] ?? 0)
        );
    } catch (ConexionBDException $e) {
        mostrarErrorConexion();
    } catch (PDOException $e) {
        error_log('[Autenticación] Error al validar la sesión: ' . $e->getMessage());
        mostrarErrorConexion();
    }

    if ($usuario === null || (int) $usuario['activo'] !== 1) {
        $_SESSION = [];
        session_regenerate_id(true);
        setFlash('error', 'Tu cuenta ya no está activa. Iniciá sesión nuevamente.');
        header('Location: index.php?c=auth&a=login');
        exit;
    }

    $_SESSION['usuario'] = (string) $usuario['usuario'];
    $_SESSION['rol'] = (string) $usuario['rol'];
}

function usuarioActual(): string
{
    iniciarSesion();

    return (string) ($_SESSION['usuario'] ?? 'usuario');
}

function rolActual(): ?string
{
    iniciarSesion();

    return isset($_SESSION['rol']) ? (string) $_SESSION['rol'] : null;
}

function rolActualPresentable(): string
{
    return ucfirst(rolActual() ?? 'lector');
}

// Determina si el usuario logueado tiene permisos de ABM sobre libros.

function puedeGestionarLibros(): bool
{
    $rol = rolActual();

    return $rol === 'admin' || $rol === 'bibliotecario';
}

 /* Exige permisos de ABM. Si no tiene, se cancela la operación
  y se redirige al catálogo con un mensaje explicativo */

function requerirPermisoGestion(): void
{
    requerirLogin();

    if (!puedeGestionarLibros()) {
        setFlash('error', 'Acceso denegado: tu rol de «' . escapar(rolActual() ?? 'desconocido') . '» no tiene permisos para realizar altas, bajas o modificaciones de libros.');
        header('Location: index.php?c=libro&a=index');
        exit;
    }
}

function requerirAdmin(): void
{
    requerirLogin();

    if (rolActual() !== 'admin') {
        setFlash('error', 'Acceso denegado: solo un administrador puede gestionar cuentas de usuario.');
        header('Location: index.php?c=home');
        exit;
    }
}

// Escapa texto para mostrarlo de forma segura en HTML
function escapar(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

// CSRF: protege los formularios del ABM contra envíos falsificados.

// Devuelve (creando si hace falta) el token CSRF de la sesión

function tokenCsrf(): string
{
    iniciarSesion();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

// Genera el input oculto con el token CSRF listo para insertar en el <form>
function campoCsrf(): string
{
    return '<input type="hidden" name="csrf_token" value="' . escapar(tokenCsrf()) . '">';
}

// Corta la ejecución si el token CSRF recibido no es válido
function validarCsrf(): void
{
    iniciarSesion();

    $recibido = (string) ($_POST['csrf_token'] ?? '');
    $esperado = (string) ($_SESSION['csrf_token'] ?? '');

    if ($esperado === '' || !hash_equals($esperado, $recibido)) {
        http_response_code(400);
        die('Solicitud inválida (token de seguridad incorrecto). Vuelva a intentar.');
    }
}

// Mensajes flash (se muestran una sola vez tras una redirección)

// Guarda un mensaje flash para el siguiente request

function setFlash(string $tipo, string $mensaje): void
{
    iniciarSesion();
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

// Recupera y elimina el mensaje flash pendiente.

function tomarFlash(): ?array
{
    iniciarSesion();

    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}