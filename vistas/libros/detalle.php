<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Biblioteca - <?php echo escapar($libro['nombre']); ?></title>
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
        <h1 class="abm__titulo">Ficha del libro</h1>
        <p class="abm__subtitulo">Información completa del registro seleccionado</p>
      </div>
      <a href="<?php echo escapar($urlRetorno); ?>" class="btn-login btn-login--compact">&larr; Volver al listado</a>
    </div>

    <article class="libro-detalle">
      <div class="libro-detalle__encabezado">
        <div>
          <p class="libro-detalle__categoria"><?php echo escapar($libro['categoria']); ?></p>
          <h2><?php echo escapar($libro['nombre']); ?></h2>
          <p class="libro-detalle__autor">Por <?php echo escapar($libro['autor']); ?></p>
        </div>
        <span class="etiqueta <?php echo (int) $libro['activo'] === 1 ? 'etiqueta--ok' : 'etiqueta--baja'; ?>">
          <?php echo (int) $libro['activo'] === 1 ? 'Activo' : 'Dado de baja'; ?>
        </span>
      </div>

      <dl class="libro-detalle__datos">
        <div>
          <dt>Precio</dt>
          <dd>$ <?php echo number_format((float) $libro['precio'], 2, ',', '.'); ?></dd>
        </div>
        <div>
          <dt>Stock</dt>
          <dd><?php echo (int) $libro['stock']; ?></dd>
        </div>
        <?php if ($puedeGestionar) : ?>
          <div>
            <dt>Fecha de alta</dt>
            <dd><?php echo escapar((string) $libro['creado_en']); ?></dd>
          </div>
          <div>
            <dt>Identificador</dt>
            <dd>#<?php echo (int) $libro['id']; ?></dd>
          </div>
        <?php endif; ?>
      </dl>

      <section class="libro-detalle__descripcion">
        <h3>Descripción</h3>
        <p><?php echo nl2br(escapar((string) ($libro['descripcion'] ?? ''))); ?></p>
      </section>

      <?php if ($puedeGestionar) : ?>
        <div class="libro-detalle__acciones">
          <a href="index.php?c=libro&amp;a=editar&amp;id=<?php echo (int) $libro['id']; ?>" class="btn-accion">Editar libro</a>
        </div>
      <?php endif; ?>
    </article>
  </section>
</main>
<?php include dirname(__DIR__) . '/layouts/footer.php'; ?>
</body>
</html>
