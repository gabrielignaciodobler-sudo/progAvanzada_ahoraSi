<?php

// ABM administrativo de cuentas de usuario.

declare(strict_types=1);

class UsuarioControlador extends Controlador
{
    public function index(): void
    {
        requerirAdmin();

        $usuarios = [];
        $errorPagina = '';

        try {
            $usuarios = (new Usuario(conectarBD()))->listarCuentas();
        } catch (PDOException $e) {
            error_log('[Usuarios] Error al listar cuentas: ' . $e->getMessage());
            $errorPagina = MENSAJE_ERROR_CONEXION;
        }

        $this->renderizar('usuarios/listar', [
            'usuarios'   => $usuarios,
            'flash'      => tomarFlash(),
            'errorPagina' => $errorPagina,
        ]);
    }

    public function crear(): void
    {
        requerirAdmin();

        $datos = $_SESSION['usuario_form_datos'] ?? [];
        $errores = $_SESSION['usuario_form_errores'] ?? [];
        unset($_SESSION['usuario_form_datos'], $_SESSION['usuario_form_errores']);

        $this->renderizarFormulario([
            'id'      => 0,
            'usuario' => (string) ($datos['usuario'] ?? ''),
            'rol'     => (string) ($datos['rol'] ?? 'lector'),
        ], false, $errores);
    }

    public function editar(): void
    {
        requerirAdmin();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            setFlash('error', 'Identificador de cuenta inválido.');
            $this->redirigir('index.php?c=usuario');
        }

        try {
            $usuario = (new Usuario(conectarBD()))->buscarCuentaPorId($id);
        } catch (PDOException $e) {
            error_log('[Usuarios] Error al buscar cuenta: ' . $e->getMessage());
            setFlash('error', MENSAJE_ERROR_CONEXION);
            $this->redirigir('index.php?c=usuario');
        }

        if ($usuario === null) {
            setFlash('error', 'La cuenta solicitada no existe.');
            $this->redirigir('index.php?c=usuario');
        }

        $datos = $_SESSION['usuario_form_datos'] ?? [];
        $errores = $_SESSION['usuario_form_errores'] ?? [];
        unset($_SESSION['usuario_form_datos'], $_SESSION['usuario_form_errores']);

        $this->renderizarFormulario([
            'id'      => $id,
            'usuario' => (string) ($datos['usuario'] ?? $usuario['usuario']),
            'rol'     => (string) ($datos['rol'] ?? $usuario['rol']),
        ], true, $errores);
    }

    public function guardar(): void
    {
        requerirAdmin();

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->redirigir('index.php?c=usuario');
        }

        validarCsrf();

        $idRecibido = filter_var($_POST['id'] ?? '0', FILTER_VALIDATE_INT);
        if ($idRecibido === false) {
            setFlash('error', 'Identificador de cuenta inválido.');
            $this->redirigir('index.php?c=usuario');
        }
        $id = $idRecibido;
        if ($id < 0) {
            setFlash('error', 'Identificador de cuenta inválido.');
            $this->redirigir('index.php?c=usuario');
        }

        $usuarioRecibido = $_POST['usuario'] ?? '';
        $rolRecibido = $_POST['rol'] ?? '';
        $datos = [
            'usuario' => is_string($usuarioRecibido) ? trim($usuarioRecibido) : '',
            'rol'     => is_string($rolRecibido) ? trim($rolRecibido) : '',
        ];
        $passwordRecibido = $_POST['password'] ?? '';
        $passwordValido = is_string($passwordRecibido);
        $password = $passwordValido ? $passwordRecibido : '';
        $errores = [];

        if ($datos['usuario'] === '') {
            $errores['usuario'] = 'El nombre de usuario es obligatorio.';
        } elseif (mb_strlen($datos['usuario']) > 50) {
            $errores['usuario'] = 'El nombre de usuario no puede superar los 50 caracteres.';
        }

        if (!in_array($datos['rol'], Usuario::ROLES_CUENTA, true)) {
            $errores['rol'] = 'Debe seleccionar un rol válido.';
        }

        if ($id > 0 && $id === (int) ($_SESSION['usuario_id'] ?? 0) && $datos['rol'] !== 'admin') {
            $errores['rol'] = 'No podés quitarte el rol de administrador a tu propia cuenta.';
        }

        if ($id <= 0 && $password === '') {
            $errores['password'] = 'La contraseña es obligatoria para crear una cuenta.';
        }
        if (!$passwordValido) {
            $errores['password'] = 'La contraseña recibida no es válida.';
        }

        if ($errores !== []) {
            $this->guardarErroresFormulario($datos, $errores, $id);
        }

        try {
            $modelo = new Usuario(conectarBD());
            if ($modelo->existeUsuario($datos['usuario'], $id)) {
                $this->guardarErroresFormulario(
                    $datos,
                    ['usuario' => 'Ese nombre de usuario ya está en uso.'],
                    $id
                );
            }

            if ($id > 0) {
                if (!$modelo->actualizarCuenta($id, $datos['usuario'], $password, $datos['rol'])) {
                    setFlash('error', 'La cuenta solicitada ya no existe.');
                    $this->redirigir('index.php?c=usuario');
                }
                if ($id === (int) ($_SESSION['usuario_id'] ?? 0)) {
                    $_SESSION['usuario'] = $datos['usuario'];
                    $_SESSION['rol'] = $datos['rol'];
                }
                setFlash('exito', 'Cuenta modificada correctamente.');
            } else {
                $nuevoId = $modelo->crearCuenta($datos['usuario'], $password, $datos['rol']);
                setFlash('exito', 'Cuenta creada correctamente con el ID #' . $nuevoId . '.');
            }
        } catch (DomainException $e) {
            $this->guardarErroresFormulario($datos, ['rol' => $e->getMessage()], $id);
        } catch (PDOException $e) {
            error_log('[Usuarios] Error al guardar cuenta: ' . $e->getMessage());
            if ($e->getCode() === '23000') {
                $this->guardarErroresFormulario(
                    $datos,
                    ['usuario' => 'Ese nombre de usuario ya está en uso.'],
                    $id
                );
            }
            setFlash('error', MENSAJE_ERROR_CONEXION);
        }

        $this->redirigir('index.php?c=usuario');
    }

    public function eliminar(): void
    {
        requerirAdmin();

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->redirigir('index.php?c=usuario');
        }

        validarCsrf();

        $idRecibido = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
        $id = $idRecibido === false ? 0 : $idRecibido;
        $accion = $_POST['accion'] ?? '';
        $idActual = (int) ($_SESSION['usuario_id'] ?? 0);

        if (!is_string($accion) || !in_array($accion, ['activar', 'desactivar'], true)) {
            setFlash('error', 'Acción de cuenta inválida.');
            $this->redirigir('index.php?c=usuario');
        }

        $activa = $accion === 'activar';
        if ($id <= 0) {
            setFlash('error', 'Identificador de cuenta inválido.');
            $this->redirigir('index.php?c=usuario');
        }

        if (!$activa && $id === $idActual) {
            setFlash('error', 'No podés desactivar tu propia cuenta mientras tenés una sesión activa.');
            $this->redirigir('index.php?c=usuario');
        }

        try {
            $cambio = (new Usuario(conectarBD()))->establecerCuentaActiva($id, $activa);
            setFlash(
                $cambio ? 'exito' : 'error',
                $cambio
                    ? ($activa ? 'Cuenta reactivada correctamente.' : 'Cuenta desactivada correctamente.')
                    : 'La cuenta ya tenía ese estado o no existe.'
            );
        } catch (DomainException $e) {
            setFlash('error', $e->getMessage());
        } catch (PDOException $e) {
            error_log('[Usuarios] Error al cambiar estado de cuenta: ' . $e->getMessage());
            setFlash('error', MENSAJE_ERROR_CONEXION);
        }

        $this->redirigir('index.php?c=usuario');
    }

    private function renderizarFormulario(array $usuario, bool $esEdicion, array $errores): void
    {
        $this->renderizar('usuarios/formulario', [
            'usuarioCuenta' => $usuario,
            'esEdicion'     => $esEdicion,
            'errores'       => $errores,
        ]);
    }

    private function guardarErroresFormulario(array $datos, array $errores, int $id): void
    {
        $_SESSION['usuario_form_datos'] = $datos;
        $_SESSION['usuario_form_errores'] = $errores;
        $this->redirigir($id > 0
            ? "index.php?c=usuario&a=editar&id={$id}"
            : 'index.php?c=usuario&a=crear');
    }
}
