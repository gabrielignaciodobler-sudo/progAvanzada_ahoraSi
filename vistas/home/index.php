<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Biblioteca - Inicio</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css?v=<?php echo filemtime(dirname(__DIR__, 2) . '/assets/css/style.css'); ?>">
</head>

<body>
<?php include dirname(__DIR__) . '/layouts/header.php'; ?>
<main class="home-hero">
  <p class="bienvenida-central">Bienvenido a la Biblioteca de la Universidad Autónoma de Entre Rios <br>Por favor inicia sesión para administrar tu contenido</p>
  <div class="hero-acciones">
    <a href="index.php?c=auth&a=login" class="btn-login btn-login--compact">Iniciar sesión</a>
    <a href="index.php?c=auth&a=registro" class="btn-login btn-login--compact btn-login--secundario">Registrar cuenta</a>
  </div>
</main>
<?php include dirname(__DIR__) . '/layouts/footer.php'; ?>  
</body>
</html>
