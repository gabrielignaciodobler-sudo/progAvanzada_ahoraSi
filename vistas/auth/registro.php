<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro - Biblioteca</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css?v=<?php echo filemtime(dirname(__DIR__, 2) . '/assets/css/style.css'); ?>">
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body class="auth-page">
<?php include dirname(__DIR__) . '/layouts/header.php'; ?>
<main>

  <div class="ornamento" aria-hidden="true">
    <span class="ornamento__linea"></span>
    <svg class="ornamento__icono" viewBox="0 0 40 28" xmlns="http://www.w3.org/2000/svg">
      <path d="M20 4 C15 1 8 1 3 3.5 V22 C8 19.5 15 19.5 20 22 C25 19.5 32 19.5 37 22 V3.5 C32 1 25 1 20 4 Z" fill="none" stroke="currentColor" stroke-width="1.1" stroke-linejoin="round"/>
      <path d="M20 4 V22" fill="none" stroke="currentColor" stroke-width="1.1"/>
    </svg>
    <span class="ornamento__linea"></span>
  </div>

  <div class="login-area">

    <div class="decoracion decoracion--izq" aria-hidden="true">
      <svg viewBox="0 0 100 220" xmlns="http://www.w3.org/2000/svg">
        <rect x="14" y="30" width="16" height="150" rx="1.5" transform="rotate(-7 22 105)" fill="none" stroke="currentColor" stroke-width="1"/>
        <rect x="34" y="24" width="16" height="156" rx="1.5" transform="rotate(-2 42 102)" fill="none" stroke="currentColor" stroke-width="1"/>
        <rect x="54" y="34" width="16" height="146" rx="1.5" transform="rotate(4 62 107)" fill="none" stroke="currentColor" stroke-width="1"/>
        <rect x="10" y="182" width="80" height="14" rx="1.5" fill="none" stroke="currentColor" stroke-width="1"/>
        <rect x="16" y="198" width="68" height="12" rx="1.5" fill="none" stroke="currentColor" stroke-width="1"/>
      </svg>
    </div>

    <form class="form--registro" action="index.php?c=auth&a=registro" method="post">
        <input type="text" id="usuario" name="usuario" placeholder="Nombre de usuario" maxlength="50" required autocomplete="username">

        <input type="password" id="password" name="password" placeholder="Contraseña" required autocomplete="new-password">

        <div class="captcha-box">
          <div class="g-recaptcha" data-sitekey="<?php echo escapar($recaptchaSiteKey); ?>" data-callback="onRecaptchaSuccess" data-expired-callback="onRecaptchaExpired"></div>
        </div>

        <?php if (!empty($error)) : ?>
            <p class="error-message"><?php echo escapar($error); ?></p>
        <?php endif; ?>

        <button type="submit" id="btnSubmit" disabled>Crear cuenta</button>

        <p class="auth-enlace">
          ¿Ya tenés cuenta? <a href="index.php?c=auth&a=login">Iniciá sesión aquí</a>
        </p>
    </form>

    <div class="decoracion decoracion--der" aria-hidden="true">
      <svg viewBox="0 0 100 220" xmlns="http://www.w3.org/2000/svg">
        <path d="M28 200 C28 150 30 90 66 34" fill="none" stroke="currentColor" stroke-width="1"/>
        <path d="M66 34 C70 40 70 48 64 52 C58 48 58 40 66 34 Z" fill="none" stroke="currentColor" stroke-width="1"/>
        <path d="M50 92 C56 88 62 90 66 96" fill="none" stroke="currentColor" stroke-width="1"/>
        <path d="M42 118 C48 114 54 116 58 122" fill="none" stroke="currentColor" stroke-width="1"/>
        <path d="M34 144 C40 140 46 142 50 148" fill="none" stroke="currentColor" stroke-width="1"/>
        <ellipse cx="26" cy="204" rx="16" ry="7" fill="none" stroke="currentColor" stroke-width="1"/>
        <path d="M12 204 L14 196 C18 193 34 193 38 196 L40 204" fill="none" stroke="currentColor" stroke-width="1"/>
      </svg>
    </div>

  </div>

  <p class="frase">— Sumate a nuestra biblioteca. —</p>

</main>

<script src="assets/js/scriptLogin.js" type="text/javascript"></script>
<?php include dirname(__DIR__) . '/layouts/footer.php'; ?>  
</body>
</html>
