<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Biblioteca - Cuentas de usuario</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css?v=<?php echo filemtime(dirname(__DIR__, 2) . '/assets/css/style.css'); ?>">
</head>
<body>
<?php include dirname(__DIR__) . '/layouts/header.php'; ?>
<main>
  <section class="abm">
    <div class="abm__barra">
      <div>
        <h1 class="abm__titulo">Cuentas de usuario</h1>
        <p class="abm__subtitulo">Alta &middot; Baja &middot; Modificación</p>
      </div>
      <a href="index.php?c=usuario&a=crear" class="btn-login btn-login--compact">+ Nueva cuenta</a>
    </div>

    <?php if ($flash !== null) : ?>
      <p class="mensaje mensaje--<?php echo escapar($flash['tipo']); ?>">
        <?php echo escapar($flash['mensaje']); ?>
      </p>
    <?php endif; ?>

    <?php if ($errorPagina !== '') : ?>
      <p class="mensaje mensaje--error"><?php echo escapar($errorPagina); ?></p>
    <?php endif; ?>

    <?php if ($usuarios === []) : ?>
      <p class="abm__vacio">No hay cuentas para mostrar.</p>
    <?php else : ?>
      <div class="tabla-scroll">
        <table class="tabla-libros">
          <thead>
            <tr>
              <th>#</th>
              <th>Usuario</th>
              <th>Rol</th>
              <th>Estado</th>
              <th>Creada</th>
              <th class="col-acciones">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($usuarios as $fila) : ?>
              <?php $activa = (int) $fila['activo'] === 1; ?>
              <tr class="<?php echo $activa ? '' : 'fila-inactiva'; ?>">
                <td><?php echo (int) $fila['id']; ?></td>
                <td>
                  <?php echo escapar($fila['usuario']); ?>
                  <?php if ((int) $fila['id'] === (int) ($_SESSION['usuario_id'] ?? 0)) : ?>
                    <span class="etiqueta etiqueta--ok">Tu sesión</span>
                  <?php endif; ?>
                </td>
                <td><?php echo escapar($fila['rol']); ?></td>
                <td>
                  <span class="etiqueta <?php echo $activa ? 'etiqueta--ok' : 'etiqueta--baja'; ?>">
                    <?php echo $activa ? 'Activa' : 'Desactivada'; ?>
                  </span>
                </td>
                <td><?php echo escapar(date('d/m/Y', strtotime((string) $fila['creado_en']))); ?></td>
                <td class="col-acciones">
                  <div class="acciones">
                    <a href="index.php?c=usuario&a=editar&id=<?php echo (int) $fila['id']; ?>" class="btn-accion">Editar</a>
                    <?php if ((int) $fila['id'] !== (int) ($_SESSION['usuario_id'] ?? 0)) : ?>
                      <form action="index.php?c=usuario&a=eliminar" method="post" class="form-inline"
                            onsubmit="return confirm('<?php echo $activa ? '¿Desactivar esta cuenta?' : '¿Reactivar esta cuenta?'; ?>');">
                        <?php echo campoCsrf(); ?>
                        <input type="hidden" name="id" value="<?php echo (int) $fila['id']; ?>">
                        <input type="hidden" name="accion" value="<?php echo $activa ? 'desactivar' : 'activar'; ?>">
                        <button type="submit" class="btn-accion <?php echo $activa ? 'btn-accion--peligro' : 'btn-accion--ok'; ?>">
                          <?php echo $activa ? 'Eliminar' : 'Reactivar'; ?>
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</main>
<?php include dirname(__DIR__) . '/layouts/footer.php'; ?>
</body>
</html>
