<?php
require_once __DIR__ . '/php/auth.php';

requerirLogin();
$puedeGestionar = puedeGestionarLibros();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Biblioteca - Inicio</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=<?php echo filemtime(__DIR__ . '/css/style.css'); ?>">
</head>

<body>
<?php include("php/header.php"); ?>
<main class="home-hero">
  <p class="bienvenida-central">¡Bienvenido <?php echo escapar(usuarioActual()); ?>!, ¿que vamos a leer hoy?</p>
  <a href="libros.php" class="btn-login btn-login--compact">
    <?php echo $puedeGestionar ? 'Administrar catálogo de libros' : 'Ver catálogo de libros'; ?>
  </a>
</main>
<?php include("php/footer.php"); ?>  
</body>
</html>