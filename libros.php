<?php
/**
 * ============================================================================
 *  libros.php
 *  LECTURA (Read) del ABM: listado de libros + buscador + acceso a las
 *  acciones de alta, modificación y baja.
 * ============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/modelos/Libro.php';

requerirLogin();

$puedeGestionar   = puedeGestionarLibros();
$flash            = tomarFlash();
$errorPagina      = '';
$libros           = [];

$busqueda         = trim((string) ($_GET['q'] ?? ''));
$incluirInactivos = $puedeGestionar && isset($_GET['inactivos']) && $_GET['inactivos'] === '1';

try {
    $pdo   = conectarBD();
    $libro = new Libro($pdo);
    $libros = $libro->listar($incluirInactivos, $busqueda);
} catch (PDOException $e) {
    error_log('[Libros] Error de consulta: ' . $e->getMessage());
    $errorPagina = MENSAJE_ERROR_CONEXION;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Biblioteca - Catálogo</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=<?php echo filemtime(__DIR__ . '/css/style.css'); ?>">
</head>
<body>
<?php include __DIR__ . '/php/header.php'; ?>
<main>
  <section class="abm">

    <div class="abm__barra">
      <div>
        <h1 class="abm__titulo">Catálogo de Libros</h1>
        <p class="abm__subtitulo">
          <?php echo $puedeGestionar ? 'Alta &middot; Baja &middot; Modificación &middot; Consulta' : 'Consulta de libros disponibles'; ?>
        </p>
      </div>
      <?php if ($puedeGestionar) : ?>
        <a href="libro_formulario.php" class="btn-login btn-login--compact">+ Nuevo libro</a>
      <?php endif; ?>
    </div>

    <?php if ($flash !== null) : ?>
      <p class="mensaje mensaje--<?php echo escapar($flash['tipo']); ?>">
        <?php echo escapar($flash['mensaje']); ?>
      </p>
    <?php endif; ?>

    <?php if ($errorPagina !== '') : ?>
      <p class="mensaje mensaje--error"><?php echo escapar($errorPagina); ?></p>
    <?php endif; ?>

    <form method="get" action="libros.php" class="abm__filtros">
      <input type="text" name="q" placeholder="Buscar por título o autor"
             value="<?php echo escapar($busqueda); ?>">
      <?php if ($puedeGestionar) : ?>
        <label class="abm__check">
          <input type="checkbox" name="inactivos" value="1" <?php echo $incluirInactivos ? 'checked' : ''; ?>>
          Mostrar dados de baja
        </label>
      <?php endif; ?>
      <button type="submit">Buscar</button>
      <?php if ($busqueda !== '' || $incluirInactivos) : ?>
        <a href="libros.php" class="abm__limpiar">Limpiar</a>
      <?php endif; ?>
    </form>

    <?php if ($libros === []) : ?>
      <p class="abm__vacio">No hay libros para mostrar<?php echo $busqueda !== '' ? ' con ese criterio de búsqueda' : ''; ?>.</p>
    <?php else : ?>
      <div class="tabla-scroll">
        <table class="tabla-libros">
          <thead>
            <tr>
              <th>#</th>
              <th>Título</th>
              <th>Autor</th>
              <th>Categoría</th>
              <th class="col-num">Precio</th>
              <th class="col-num">Stock</th>
              <th>Estado</th>
              <?php if ($puedeGestionar) : ?>
                <th class="col-acciones">Acciones</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($libros as $fila) : ?>
              <?php $activo = (int) $fila['activo'] === 1; ?>
              <tr class="<?php echo $activo ? '' : 'fila-inactiva'; ?>">
                <td><?php echo (int) $fila['id']; ?></td>
                <td><?php echo escapar($fila['nombre']); ?></td>
                <td><?php echo escapar($fila['autor']); ?></td>
                <td><?php echo escapar($fila['categoria']); ?></td>
                <td class="col-num">$ <?php echo number_format((float) $fila['precio'], 2, ',', '.'); ?></td>
                <td class="col-num"><?php echo (int) $fila['stock']; ?></td>
                <td>
                  <span class="etiqueta <?php echo $activo ? 'etiqueta--ok' : 'etiqueta--baja'; ?>">
                    <?php echo $activo ? 'Activo' : 'Dado de baja'; ?>
                  </span>
                </td>
                <?php if ($puedeGestionar) : ?>
                  <td class="col-acciones">
                    <div class="acciones">
                      <a href="libro_formulario.php?id=<?php echo (int) $fila['id']; ?>" class="btn-accion">Editar</a>

                      <?php if ($activo) : ?>
                        <form action="libro_eliminar.php" method="post" class="form-inline"
                              onsubmit="return confirm('¿Confirmás dar de baja «<?php echo escapar($fila['nombre']); ?>»?');">
                          <?php echo campoCsrf(); ?>
                          <input type="hidden" name="id" value="<?php echo (int) $fila['id']; ?>">
                          <input type="hidden" name="accion" value="eliminar">
                          <button type="submit" class="btn-accion btn-accion--peligro">Baja</button>
                        </form>
                      <?php else : ?>
                        <form action="libro_eliminar.php" method="post" class="form-inline"
                              onsubmit="return confirm('¿Reactivar «<?php echo escapar($fila['nombre']); ?>»?');">
                          <?php echo campoCsrf(); ?>
                          <input type="hidden" name="id" value="<?php echo (int) $fila['id']; ?>">
                          <input type="hidden" name="accion" value="restaurar">
                          <button type="submit" class="btn-accion btn-accion--ok">Reactivar</button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  </section>
</main>
<?php include __DIR__ . '/php/footer.php'; ?>
</body>
</html>
