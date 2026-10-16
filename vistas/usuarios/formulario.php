<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Biblioteca - <?php echo $esEdicion ? 'Editar cuenta' : 'Nueva cuenta'; ?></title>
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
        <h1 class="abm__titulo"><?php echo $esEdicion ? 'Editar cuenta' : 'Nueva cuenta'; ?></h1>
        <p class="abm__subtitulo"><?php echo $esEdicion ? 'Modificar usuario, rol o contraseña' : 'Crear una cuenta para el sistema'; ?></p>
      </div>
      <a href="index.php?c=usuario" class="btn-login btn-login--compact">&larr; Volver a cuentas</a>
    </div>

    <form action="index.php?c=usuario&a=guardar" method="post" class="form-abm" novalidate>
      <?php echo campoCsrf(); ?>
      <input type="hidden" name="id" value="<?php echo (int) $usuarioCuenta['id']; ?>">

      <div class="campo">
        <label for="usuario">Nombre de usuario <span class="req">*</span></label>
        <input type="text" id="usuario" name="usuario" maxlength="50" required autocomplete="username"
               value="<?php echo escapar($usuarioCuenta['usuario']); ?>">
        <?php if (isset($errores['usuario'])) : ?>
          <span class="error-campo"><?php echo escapar($errores['usuario']); ?></span>
        <?php endif; ?>
      </div>

      <div class="campo">
        <label for="password">Contraseña
          <?php if ($esEdicion) : ?>
            (opcional)
          <?php else : ?>
            <span class="req">*</span>
          <?php endif; ?>
        </label>
        <input type="password" id="password" name="password" autocomplete="new-password"
               <?php echo $esEdicion ? '' : 'required'; ?>>
        <?php if ($esEdicion) : ?>
          <small>Dejala vacía para conservar la contraseña actual.</small>
        <?php endif; ?>
        <?php if (isset($errores['password'])) : ?>
          <span class="error-campo"><?php echo escapar($errores['password']); ?></span>
        <?php endif; ?>
      </div>

      <div class="campo">
        <label for="rol">Rol <span class="req">*</span></label>
        <select id="rol" name="rol" required>
          <option value="lector" <?php echo $usuarioCuenta['rol'] === 'lector' ? 'selected' : ''; ?>>Lector</option>
          <option value="bibliotecario" <?php echo $usuarioCuenta['rol'] === 'bibliotecario' ? 'selected' : ''; ?>>Bibliotecario</option>
          <option value="admin" <?php echo $usuarioCuenta['rol'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
        </select>
        <?php if (isset($errores['rol'])) : ?>
          <span class="error-campo"><?php echo escapar($errores['rol']); ?></span>
        <?php endif; ?>
      </div>

      <div class="form-abm__acciones">
        <button type="submit"><?php echo $esEdicion ? 'Guardar cambios' : 'Crear cuenta'; ?></button>
        <a href="index.php?c=usuario" class="btn-cancelar">Cancelar</a>
      </div>
    </form>
  </section>
</main>
<?php include dirname(__DIR__) . '/layouts/footer.php'; ?>
</body>
</html>
