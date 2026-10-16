<?php

// Controlador del ABM completo de Libros (tabla `productos`).

declare(strict_types=1);

class LibroControlador extends Controlador
{

    // Catálogo / listado de libros con buscador y filtro de inactivos.

    public function index(): void
    {
        requerirLogin();

        $puedeGestionar   = puedeGestionarLibros();
        $flash            = tomarFlash();
        $errorPagina      = '';
        $libros           = [];

        $busqueda         = trim((string) ($_GET['q'] ?? ''));
        $incluirInactivos = $puedeGestionar && isset($_GET['inactivos']) && $_GET['inactivos'] === '1';

        try {
            $pdo         = conectarBD();
            $libroModelo = new Libro($pdo);
            $libros      = $libroModelo->listar($incluirInactivos, $busqueda);
        } catch (PDOException $e) {
            error_log('[Libros] Error de consulta: ' . $e->getMessage());
            $errorPagina = MENSAJE_ERROR_CONEXION;
        }

        $this->renderizar('libros/listar', [
            'libros'           => $libros,
            'busqueda'         => $busqueda,
            'incluirInactivos' => $incluirInactivos,
            'puedeGestionar'   => $puedeGestionar,
            'flash'            => $flash,
            'errorPagina'      => $errorPagina,
        ]);
    }

    // Muestra la ficha completa de un libro del catálogo.

    public function detalle(): void
    {
        requerirLogin();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            setFlash('error', 'Identificador de libro inválido.');
            $this->redirigir('index.php?c=libro&a=index');
        }

        try {
            $libro = (new Libro(conectarBD()))->buscarPorId($id);
        } catch (PDOException $e) {
            error_log('[Libro detalle] Error de consulta: ' . $e->getMessage());
            setFlash('error', MENSAJE_ERROR_CONEXION);
            $this->redirigir('index.php?c=libro&a=index');
        }

        if ($libro === null || ((int) $libro['activo'] !== 1 && !puedeGestionarLibros())) {
            setFlash('error', 'El libro solicitado no está disponible.');
            $this->redirigir('index.php?c=libro&a=index');
        }

        $busqueda = trim((string) ($_GET['q'] ?? ''));
        $incluirInactivos = puedeGestionarLibros()
            && (($_GET['inactivos'] ?? '') === '1');
        $parametrosRetorno = ['c' => 'libro', 'a' => 'index'];
        if ($busqueda !== '') {
            $parametrosRetorno['q'] = $busqueda;
        }
        if ($incluirInactivos) {
            $parametrosRetorno['inactivos'] = '1';
        }

        $this->renderizar('libros/detalle', [
            'libro' => $libro,
            'puedeGestionar' => puedeGestionarLibros(),
            'urlRetorno' => 'index.php?' . http_build_query($parametrosRetorno),
        ]);
    }

    // Muestra el formulario para dar de alta un nuevo libro.

    public function crear(): void
    {
        requerirLogin();
        requerirPermisoGestion();

        $errores = $_SESSION['form_errores'] ?? [];
        $datos   = $_SESSION['form_datos']   ?? [];
        unset($_SESSION['form_errores'], $_SESSION['form_datos']);

        $libro = [
            'id'          => 0,
            'nombre'      => (string) ($datos['nombre'] ?? ''),
            'autor'       => (string) ($datos['autor'] ?? ''),
            'descripcion' => (string) ($datos['descripcion'] ?? ''),
            'precio'      => (string) ($datos['precio'] ?? ''),
            'stock'       => (string) ($datos['stock'] ?? ''),
            'categoria'   => (string) ($datos['categoria'] ?? ''),
        ];

        $this->renderizar('libros/formulario', [
            'libro'       => $libro,
            'esEdicion'   => false,
            'errores'     => $errores,
            'categorias'  => Libro::CATEGORIAS,
            'errorPagina' => '',
        ]);
    }

    //Muestra el formulario para editar un libro existente.

    public function editar(): void
    {
        requerirLogin();
        requerirPermisoGestion();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            setFlash('error', 'Identificador de libro inválido.');
            $this->redirigir('index.php?c=libro&a=index');
        }

        $errores = $_SESSION['form_errores'] ?? [];
        $datos   = $_SESSION['form_datos']   ?? [];
        unset($_SESSION['form_errores'], $_SESSION['form_datos']);

        $errorPagina = '';
        $libro       = null;

        try {
            $pdo         = conectarBD();
            $libroModelo = new Libro($pdo);
            $registro    = $libroModelo->buscarPorId($id);

            if ($registro === null) {
                setFlash('error', 'El libro solicitado no existe.');
                $this->redirigir('index.php?c=libro&a=index');
            }

            $libro = [
                'id'          => (int) $registro['id'],
                'nombre'      => (string) ($datos['nombre'] ?? $registro['nombre']),
                'autor'       => (string) ($datos['autor'] ?? $registro['autor']),
                'descripcion' => (string) ($datos['descripcion'] ?? $registro['descripcion']),
                'precio'      => (string) ($datos['precio'] ?? $registro['precio']),
                'stock'       => (string) ($datos['stock'] ?? $registro['stock']),
                'categoria'   => (string) ($datos['categoria'] ?? $registro['categoria']),
            ];
        } catch (PDOException $e) {
            error_log('[Libro editar] Error de consulta: ' . $e->getMessage());
            $errorPagina = MENSAJE_ERROR_CONEXION;
            $libro = [
                'id'          => $id,
                'nombre'      => '',
                'autor'       => '',
                'descripcion' => '',
                'precio'      => '',
                'stock'       => '',
                'categoria'   => '',
            ];
        }

        $this->renderizar('libros/formulario', [
            'libro'       => $libro,
            'esEdicion'   => true,
            'errores'     => $errores,
            'categorias'  => Libro::CATEGORIAS,
            'errorPagina' => $errorPagina,
        ]);
    }

    // Procesa la inserción o actualización de un libro (POST).

    public function guardar(): void
    {
        requerirLogin();
        requerirPermisoGestion();

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->redirigir('index.php?c=libro&a=index');
        }

        validarCsrf();

        $id    = (int) ($_POST['id'] ?? 0);
        $datos = [
            'nombre'      => $_POST['nombre'] ?? '',
            'autor'       => $_POST['autor'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'precio'      => $_POST['precio'] ?? '',
            'stock'       => $_POST['stock'] ?? '',
            'categoria'   => $_POST['categoria'] ?? '',
        ];

        $errores = Libro::validar($datos);

        if ($errores !== []) {
            $_SESSION['form_errores'] = $errores;
            $_SESSION['form_datos']   = $datos + ['id' => $id];
            $urlRetorno = $id > 0
                ? "index.php?c=libro&a=editar&id={$id}"
                : "index.php?c=libro&a=crear";
            $this->redirigir($urlRetorno);
        }

        $datosLimpios = Libro::normalizar($datos);

        try {
            $pdo         = conectarBD();
            $libroModelo = new Libro($pdo);

            if ($id > 0) {
                $libroModelo->actualizar($id, $datosLimpios);
                setFlash('exito', 'Libro modificado correctamente.');
            } else {
                $nuevoId = $libroModelo->crear($datosLimpios);
                setFlash('exito', 'Libro dado de alta con el ID #' . $nuevoId . '.');
            }
        } catch (PDOException $e) {
            error_log('[Libro guardar] Error de persistencia: ' . $e->getMessage());
            setFlash('error', MENSAJE_ERROR_CONEXION);
        }

        $this->redirigir('index.php?c=libro&a=index');
    }

    
    // Procesa la baja lógica o reactivación de un libro (POST).

    public function eliminar(): void
    {
        requerirLogin();
        requerirPermisoGestion();

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $this->redirigir('index.php?c=libro&a=index');
        }

        validarCsrf();

        $id     = (int) ($_POST['id'] ?? 0);
        $accion = (string) ($_POST['accion'] ?? 'eliminar');

        if ($id <= 0) {
            setFlash('error', 'Identificador de libro inválido.');
            $this->redirigir('index.php?c=libro&a=index');
        }

        try {
            $pdo         = conectarBD();
            $libroModelo = new Libro($pdo);

            if ($accion === 'restaurar') {
                $libroModelo->restaurar($id)
                    ? setFlash('exito', "El libro #{$id} fue reactivado.")
                    : setFlash('error', "No se pudo reactivar el libro #{$id}.");
            } else {
                $libroModelo->eliminar($id)
                    ? setFlash('exito', "El libro #{$id} fue dado de baja.")
                    : setFlash('error', "No se pudo dar de baja el libro #{$id}.");
            }
        } catch (PDOException $e) {
            error_log('[Libro eliminar] Error de persistencia: ' . $e->getMessage());
            setFlash('error', MENSAJE_ERROR_CONEXION);
        }

        $this->redirigir('index.php?c=libro&a=index');
    }
}