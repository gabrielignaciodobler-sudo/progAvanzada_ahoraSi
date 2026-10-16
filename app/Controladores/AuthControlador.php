<?php
/**
 * ============================================================================
 *  app/Controladores/AuthControlador.php
 *  Controlador de autenticación, registro y cierre de sesión.
 * ============================================================================
 */

declare(strict_types=1);

class AuthControlador extends Controlador
{
    private const RECAPTCHA_SITE_KEY   = '6Le1TM0tAAAAACvxeirW5N62-dAb8khdivkvkn41';
    private const RECAPTCHA_SECRET_KEY = '6Le1TM0tAAAAAFlzM3X-jchqnZOlonf3aeP4jIPy';

    /**
     * Muestra el formulario de inicio de sesión.
     */
    public function mostrarLogin(): void
    {
        if (usuarioLogueado()) {
            $this->redirigir('index.php?c=home&a=inicio');
        }

        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['error']);

        $exito = $_SESSION['exito'] ?? '';
        unset($_SESSION['exito']);

        $this->renderizar('auth/login', [
            'error'            => $error,
            'exito'            => $exito,
            'recaptchaSiteKey' => self::RECAPTCHA_SITE_KEY,
        ]);
    }

    /**
     * Procesa las credenciales de inicio de sesión (POST).
     */
    public function login(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->redirigir('index.php?c=auth&a=login');
        }

        $usuario           = trim((string) ($_POST['usuario'] ?? ''));
        $password          = (string) ($_POST['password'] ?? '');
        $recaptchaResponse = (string) ($_POST['g-recaptcha-response'] ?? '');

        // 1) Validación de reCAPTCHA
        if (!$this->verificarRecaptcha($recaptchaResponse)) {
            $_SESSION['error'] = $recaptchaResponse === ''
                ? 'Por favor, completá el captcha.'
                : 'Captcha incorrecto. Intentalo de nuevo.';
            $this->redirigir('index.php?c=auth&a=login');
        }

        // 2) Validación contra la Base de Datos
        try {
            $pdo        = obtenerConexion();
            $modeloUser = new Usuario($pdo);
            $datosUser  = $modeloUser->validarCredenciales($usuario, $password);
        } catch (ConexionBDException $e) {
            mostrarErrorConexion();
        } catch (PDOException $e) {
            error_log('[Login] Error de consulta: ' . $e->getMessage());
            $_SESSION['error'] = MENSAJE_ERROR_CONEXION;
            $this->redirigir('index.php?c=auth&a=login');
        }

        // 3) Comprobación de credenciales
        if ($datosUser !== null) {
            unset($_SESSION['error']);
            session_regenerate_id(true);

            $_SESSION['logueado']   = true;
            $_SESSION['usuario']    = $datosUser['usuario'];
            $_SESSION['usuario_id'] = (int) $datosUser['id'];
            $_SESSION['rol']        = $datosUser['rol'];

            $this->redirigir('index.php?c=auth&a=exitoso');
        }

        $_SESSION['error'] = 'Usuario o contraseña incorrectos.';
        $this->redirigir('index.php?c=auth&a=login');
    }

    /**
     * Muestra el formulario de registro de nuevos usuarios.
     */
    public function mostrarRegistro(): void
    {
        if (usuarioLogueado()) {
            $this->redirigir('index.php?c=home&a=inicio');
        }

        $error = $_SESSION['error'] ?? '';
        unset($_SESSION['error']);

        $this->renderizar('auth/registro', [
            'error'            => $error,
            'recaptchaSiteKey' => self::RECAPTCHA_SITE_KEY,
        ]);
    }

    /**
     * Procesa el formulario de registro (POST).
     */
    public function registro(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->redirigir('index.php?c=auth&a=registro');
        }

        $usuario           = trim((string) ($_POST['usuario'] ?? ''));
        $password          = (string) ($_POST['password'] ?? '');
        $rol               = trim((string) ($_POST['rol'] ?? ''));
        $recaptchaResponse = (string) ($_POST['g-recaptcha-response'] ?? '');

        // 1) Validación reCAPTCHA
        if (!$this->verificarRecaptcha($recaptchaResponse)) {
            $_SESSION['error'] = $recaptchaResponse === ''
                ? 'Por favor, completá el captcha.'
                : 'Captcha incorrecto. Intentalo de nuevo.';
            $this->redirigir('index.php?c=auth&a=registro');
        }

        // 2) Validación de campos requeridos
        if ($usuario === '' || $password === '' || $rol === '') {
            $_SESSION['error'] = 'Por favor, completá todos los campos requeridos.';
            $this->redirigir('index.php?c=auth&a=registro');
        }

        if (!in_array($rol, Usuario::ROLES_REGISTRO, true)) {
            $_SESSION['error'] = 'El rol seleccionado no es válido.';
            $this->redirigir('index.php?c=auth&a=registro');
        }

        // 3) Registro en BD
        try {
            $pdo        = obtenerConexion();
            $modeloUser = new Usuario($pdo);

            if ($modeloUser->existeUsuario($usuario)) {
                $_SESSION['error'] = 'El nombre de usuario ya está en uso. Por favor elegí otro.';
                $this->redirigir('index.php?c=auth&a=registro');
            }

            $modeloUser->registrar($usuario, $password, $rol);

            $_SESSION['exito'] = '¡Cuenta creada con éxito! Ya podés iniciar sesión con tus datos.';
            $this->redirigir('index.php?c=auth&a=login');

        } catch (ConexionBDException $e) {
            mostrarErrorConexion();
        } catch (PDOException $e) {
            error_log('[Registro] Error de BD: ' . $e->getMessage());
            $_SESSION['error'] = 'Ocurrió un error al registrar el usuario en la base de datos.';
            $this->redirigir('index.php?c=auth&a=registro');
        } catch (Exception $e) {
            error_log('[Registro] Error general: ' . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            $this->redirigir('index.php?c=auth&a=registro');
        }
    }

    /**
     * Cierra la sesión activa y elimina las cookies correspondientes.
     */
    public function logout(): void
    {
        iniciarSesion();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
        $this->redirigir('index.php');
    }

    /**
     * Pantalla interactiva de bienvenida y planes.
     */
    public function exitoso(): void
    {
        requerirLogin();
        $this->renderizar('auth/loginexitoso', [
            'usuario' => usuarioActual(),
        ]);
    }

    /**
     * Pantalla informativa de login denegado.
     */
    public function fallido(): void
    {
        $error = $_SESSION['error'] ?? 'Usuario o contraseña incorrectos.';
        unset($_SESSION['error']);

        $this->renderizar('auth/loginfallido', [
            'error' => $error,
        ]);
    }

    /**
     * Valida la respuesta del reCAPTCHA contra la API de Google.
     */
    private function verificarRecaptcha(string $response): bool
    {
        if ($response === '') {
            return false;
        }

        $verifyResponse = @file_get_contents(
            'https://www.google.com/recaptcha/api/siteverify?secret='
            . urlencode(self::RECAPTCHA_SECRET_KEY)
            . '&response=' . urlencode($response)
        );

        $captchaData = json_decode((string) $verifyResponse, true);

        return !empty($captchaData['success']) && $captchaData['success'] === true;
    }
}
