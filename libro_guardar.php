<?php
/**
 * ============================================================================
 *  libro_guardar.php
 *  Procesa el formulario: decide ALTA (Create) o MODIFICACIÓN (Update)
 *  según venga o no un id.
 * ============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/modelos/Libro.php';

requerirLogin();
requerirPermisoGestion();

/* Solo se acepta POST. */
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: libros.php');
    exit;
}

validarCsrf();

$id     = (int) ($_POST['id'] ?? 0);
$datos  = [
    'nombre'      => $_POST['nombre'] ?? '',
    'autor'       => $_POST['autor'] ?? '',
    'descripcion' => $_POST['descripcion'] ?? '',
    'precio'      => $_POST['precio'] ?? '',
    'stock'       => $_POST['stock'] ?? '',
    'categoria'   => $_POST['categoria'] ?? '',
];

/* Validación de reglas de negocio. */
$errores = Libro::validar($datos);

if ($errores !== []) {
    // Devolvemos al formulario conservando lo cargado y los errores.
    $_SESSION['form_errores'] = $errores;
    $_SESSION['form_datos']   = $datos + ['id' => $id];
    header('Location: libro_formulario.php' . ($id > 0 ? '?id=' . $id : ''));
    exit;
}

$datosLimpios = Libro::normalizar($datos);

try {
    $pdo    = conectarBD();
    $modelo = new Libro($pdo);

    if ($id > 0) {
        $modelo->actualizar($id, $datosLimpios);
        setFlash('exito', 'Libro modificado correctamente.');
    } else {
        $nuevoId = $modelo->crear($datosLimpios);
        setFlash('exito', 'Libro dado de alta con el ID #' . $nuevoId . '.');
    }
} catch (PDOException $e) {
    error_log('[Libro guardar] Error de persistencia: ' . $e->getMessage());
    setFlash('error', MENSAJE_ERROR_CONEXION);
}

header('Location: libros.php');
exit;
