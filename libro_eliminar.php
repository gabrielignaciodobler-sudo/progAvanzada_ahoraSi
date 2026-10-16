<?php
/**
 * ============================================================================
 *  libro_eliminar.php
 *  Procesa la BAJA (baja lógica) y la REACTIVACIÓN de un libro.
 *  Recibe por POST: id y accion (eliminar | restaurar).
 * ============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/modelos/Libro.php';

requerirLogin();
requerirPermisoGestion();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: libros.php');
    exit;
}

validarCsrf();

$id     = (int) ($_POST['id'] ?? 0);
$accion = (string) ($_POST['accion'] ?? 'eliminar');

if ($id <= 0) {
    setFlash('error', 'Identificador de libro inválido.');
    header('Location: libros.php');
    exit;
}

try {
    $pdo    = conectarBD();
    $modelo = new Libro($pdo);

    if ($accion === 'restaurar') {
        $modelo->restaurar($id)
            ? setFlash('exito', 'El libro #' . $id . ' fue reactivado.')
            : setFlash('error', 'No se pudo reactivar el libro #' . $id . '.');
    } else {
        $modelo->eliminar($id)
            ? setFlash('exito', 'El libro #' . $id . ' fue dado de baja.')
            : setFlash('error', 'No se pudo dar de baja el libro #' . $id . '.');
    }
} catch (PDOException $e) {
    error_log('[Libro eliminar] Error de persistencia: ' . $e->getMessage());
    setFlash('error', MENSAJE_ERROR_CONEXION);
}

header('Location: libros.php');
exit;
