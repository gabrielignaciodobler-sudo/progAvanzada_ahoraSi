<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Biblioteca - <?php echo $esEdicion ? 'Editar libro' : 'Nuevo libro'; ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css?v=<?php echo filemtime(dirname(__DIR__, 2) . '/assets/css/style.css'); ?>">
</head>
<body>
<?php include dirname(__DIR__) . '/layouts/header.php'; ?>
<main>
  <section class="abm abm--form">

    <div class="abm__barra">
      <div>
        <h1 class="abm__titulo"><?php echo $esEdicion ? 'Editar libro' : 'Nuevo libro'; ?></h1>
        <p class="abm__subtitulo"><?php echo $esEdicion ? 'Modificación de un registro existente' : 'Alta de un nuevo registro'; ?></p>
      </div>
      <a href="index.php?c=libro&a=index" class="btn-login btn-login--compact">&larr; Volver al catálogo</a>
    </div>

    <?php if (!empty($errorPagina)) : ?>
      <p class="mensaje mensaje--error"><?php echo escapar($errorPagina); ?></p>
    <?php endif; ?>

    <form action="index.php?c=libro&a=guardar" method="post" class="form-abm" novalidate>
      <?php echo campoCsrf(); ?>
      <input type="hidden" name="id" value="<?php echo (int) $libro['id']; ?>">

      <div class="campo">
        <label for="nombre">Título <span class="req">*</span></label>
        <input type="text" id="nombre" name="nombre" maxlength="150" required
               value="<?php echo escapar($libro['nombre']); ?>">
        <?php if (isset($errores['nombre'])) : ?>
          <span class="error-campo"><?php echo escapar($errores['nombre']); ?></span>
        <?php endif; ?>
      </div>

      <div class="campo">
        <label for="autor">Autor <span class="req">*</span></label>
        <input type="text" id="autor" name="autor" maxlength="120" required
               value="<?php echo escapar($libro['autor']); ?>">
        <?php if (isset($errores['autor'])) : ?>
          <span class="error-campo"><?php echo escapar($errores['autor']); ?></span>
        <?php endif; ?>
      </div>

      <div class="campo-doble">
        <div class="campo">
          <label for="categoria">Categoría <span class="req">*</span></label>
          <select id="categoria" name="categoria" required>
            <option value="">Seleccionar…</option>
            <?php foreach ($categorias as $categoria) : ?>
              <option value="<?php echo escapar($categoria); ?>"
                <?php echo $libro['categoria'] === $categoria ? 'selected' : ''; ?>>
                <?php echo escapar($categoria); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errores['categoria'])) : ?>
            <span class="error-campo"><?php echo escapar($errores['categoria']); ?></span>
          <?php endif; ?>
        </div>

        <div class="campo">
          <label for="precio">Precio ($) <span class="req">*</span></label>
          <input type="number" id="precio" name="precio" min="0" step="0.01" required
                 value="<?php echo escapar((string) $libro['precio']); ?>">
          <?php if (isset($errores['precio'])) : ?>
            <span class="error-campo"><?php echo escapar($errores['precio']); ?></span>
          <?php endif; ?>
        </div>

        <div class="campo">
          <label for="stock">Stock <span class="req">*</span></label>
          <input type="number" id="stock" name="stock" min="0" step="1" required
                 value="<?php echo escapar((string) $libro['stock']); ?>">
          <?php if (isset($errores['stock'])) : ?>
            <span class="error-campo"><?php echo escapar($errores['stock']); ?></span>
          <?php endif; ?>
        </div>
      </div>

      <div class="campo">
        <label for="descripcion">Descripción</label>
        <textarea id="descripcion" name="descripcion" rows="3"><?php echo escapar($libro['descripcion']); ?></textarea>
      </div>

      <div class="form-abm__acciones">
        <button type="submit"><?php echo $esEdicion ? 'Guardar cambios' : 'Dar de alta'; ?></button>
        <a href="index.php?c=libro&a=index" class="btn-cancelar">Cancelar</a>
      </div>
    </form>

  </section>
</main>
<?php include dirname(__DIR__) . '/layouts/footer.php'; ?>
</body>
</html>
