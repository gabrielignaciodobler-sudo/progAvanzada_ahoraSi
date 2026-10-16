<?php
/**
 * ============================================================================
 *  app/Helpers/auth.php
 *  Utilidades de sesión, control de acceso, CSRF y mensajes flash.
 *  Separa la "seguridad de sesión" de la lógica de negocio.
 * ============================================================================
 */

declare(strict_types=1);

/** Inicia la sesión si aún no está iniciada. */
function iniciarSesion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/** Indica si hay un usuario autenticado. */
function usuarioLogueado(): bool
{
    iniciarSesion();

    return !empty($_SESSION['logueado']) && $_SESSION['logueado'] === true;
}

/** Exige sesión activa; si no hay, redirige al login y detiene la ejecución. */
function requerirLogin(): void
{
    if (!usuarioLogueado()) {
        header('Location: index.php?c=auth&a=login');
        exit;
    }
}

/** Nombre del usuario en sesión. */
function usuarioActual(): string
{
    iniciarSesion();

    return (string) ($_SESSION['usuario'] ?? 'usuario');
}

/** Rol del usuario en sesión (null si no está definido). */
function rolActual(): ?string
{
    iniciarSesion();

    return isset($_SESSION['rol']) ? (string) $_SESSION['rol'] : null;
}

/**
 * Determina si el usuario logueado tiene permisos de ABM sobre libros.
 * Los roles con permisos son 'admin' y 'bibliotecario'.
 * El rol 'lector' NO tiene permisos de ABM (solo lectura).
 */
function puedeGestionarLibros(): bool
{
    $rol = rolActual();

    return $rol === 'admin' || $rol === 'bibliotecario';
}

/**
 * Exige permisos de ABM. Si el usuario no tiene permisos (por ejemplo, 'lector'),
 * se cancela la operación y se redirige al catálogo con un mensaje explicativo.
 */
function requerirPermisoGestion(): void
{
    requerirLogin();

    if (!puedeGestionarLibros()) {
        setFlash('error', 'Acceso denegado: tu rol de «' . escapar(rolActual() ?? 'desconocido') . '» no tiene permisos para realizar altas, bajas o modificaciones de libros.');
        header('Location: index.php?c=libro&a=index');
        exit;
    }
}

/** Escapa texto para mostrarlo de forma segura en HTML. */
function escapar(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/* ---------------------------------------------------------------------------
 * CSRF: protege los formularios del ABM contra envíos falsificados.
 * ------------------------------------------------------------------------- */

/** Devuelve (creando si hace falta) el token CSRF de la sesión. */
function tokenCsrf(): string
{
    iniciarSesion();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['csrf_token'];
}

/** Genera el input oculto con el token CSRF listo para insertar en el <form>. */
function campoCsrf(): string
{
    return '<input type="hidden" name="csrf_token" value="' . escapar(tokenCsrf()) . '">';
}

/** Corta la ejecución si el token CSRF recibido no es válido. */
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

/* ---------------------------------------------------------------------------
 * Mensajes flash (se muestran una sola vez tras una redirección).
 * ------------------------------------------------------------------------- */

/**
 * Guarda un mensaje flash para el siguiente request.
 *
 * @param string $tipo 'exito' | 'error'
 */
function setFlash(string $tipo, string $mensaje): void
{
    iniciarSesion();
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

/** Recupera y elimina el mensaje flash pendiente. */
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
