<?php
/**
 * ============================================================================
 *  procesoRegistro.php
 *  Procesa el formulario de Registro de nuevo usuario.
 *    1) Valida el reCAPTCHA contra Google.
 *    2) Valida los datos requeridos (usuario, rol, contraseña).
 *    3) Llama al modelo Usuario para insertar el nuevo usuario en la BD.
 *    4) Redirige al login con mensaje de éxito o al registro ante errores.
 * ============================================================================
 */

declare(strict_types=1);

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/modelos/Usuario.php';

$usuario           = trim((string) ($_POST['usuario'] ?? ''));
$password          = (string) ($_POST['password'] ?? '');
$rol               = trim((string) ($_POST['rol'] ?? ''));
$recaptchaResponse = (string) ($_POST['g-recaptcha-response'] ?? '');

/* ---------------------------------------------------------------------------
 * 1) Validación del reCAPTCHA
 * ------------------------------------------------------------------------- */
$recaptchaSecretKey = '6Le1TM0tAAAAAFlzM3X-jchqnZOlonf3aeP4jIPy';
$captchaValido = false;

if ($recaptchaResponse !== '') {
    $verifyResponse = @file_get_contents(
        'https://www.google.com/recaptcha/api/siteverify?secret='
        . urlencode($recaptchaSecretKey)
        . '&response=' . urlencode($recaptchaResponse)
    );
    $captchaData   = json_decode((string) $verifyResponse, true);
    $captchaValido = !empty($captchaData['success']) && $captchaData['success'] === true;
}

if (!$captchaValido) {
    $_SESSION['error'] = $recaptchaResponse === ''
        ? 'Por favor, completá el captcha.'
        : 'Captcha incorrecto. Intentalo de nuevo.';
    header('Location: registro.php');
    exit;
}

/* ---------------------------------------------------------------------------
 * 2) Validación de campos requeridos
 * ------------------------------------------------------------------------- */
if ($usuario === '' || $password === '' || $rol === '') {
    $_SESSION['error'] = 'Por favor, completá todos los campos requeridos.';
    header('Location: registro.php');
    exit;
}

if (!in_array($rol, Usuario::ROLES_REGISTRO, true)) {
    $_SESSION['error'] = 'El rol seleccionado no es válido.';
    header('Location: registro.php');
    exit;
}

/* ---------------------------------------------------------------------------
 * 3) Inserción a través del modelo Usuario
 * ------------------------------------------------------------------------- */
try {
    $pdo        = obtenerConexion();
    $modeloUser = new Usuario($pdo);

    if ($modeloUser->existeUsuario($usuario)) {
        $_SESSION['error'] = 'El nombre de usuario ya está en uso. Por favor elegí otro.';
        header('Location: registro.php');
        exit;
    }

    $modeloUser->registrar($usuario, $password, $rol);

    // Registro exitoso: guardamos mensaje y redirigimos a login.php
    $_SESSION['exito'] = '¡Cuenta creada con éxito! Ya podés iniciar sesión con tus datos.';
    header('Location: login.php');
    exit;

} catch (ConexionBDException $e) {
    mostrarErrorConexion();
} catch (PDOException $e) {
    error_log('[Registro] Error de BD: ' . $e->getMessage());
    $_SESSION['error'] = 'Ocurrió un error al registrar el usuario en la base de datos.';
    header('Location: registro.php');
    exit;
} catch (Exception $e) {
    error_log('[Registro] Error general: ' . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header('Location: registro.php');
    exit;
}

