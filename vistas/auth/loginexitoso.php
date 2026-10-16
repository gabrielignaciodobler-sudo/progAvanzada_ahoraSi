<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Exitoso - Biblioteca</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css?v=<?php echo filemtime(dirname(__DIR__, 2) . '/assets/css/style.css'); ?>">
</head>
<body class="auth-page">

<div class="resultado">

<div class="ficha-resultado">
    <span class="sello sello--ok">Acceso concedido</span>
    <h2>Buenos días, <?php echo escapar($usuario); ?></h2>
    <p>¡Te logueaste correctamente!</p>
</div>

<div class="panel-premium oculto" id="panelPremium">
    <p class="panel-premium__titulo">Has agotado tus créditos gratuitos.</p>
    <p class="panel-premium__subtitulo">Para continuar utilizando la Biblioteca, necesitás adquirir un plan premium.</p>

    <div class="planes">
        <div class="plan">
            <div class="plan__info">
                <span class="plan__nombre">Plan Estudiante</span>
                <span class="plan__precio">$4.999 / mes</span>
            </div>
            <button type="button" class="plan__boton">Obtener Premium</button>
        </div>
        <div class="plan">
            <div class="plan__info">
                <span class="plan__nombre">Plan Investigador</span>
                <span class="plan__precio">$9.999 / mes</span>
            </div>
            <button type="button" class="plan__boton">Obtener Premium</button>
         </div>
        <div class="plan">
            <div class="plan__info">
                <span class="plan__nombre">Plan UADER Premium</span>
                <span class="plan__precio">$19.999 / mes</span>
            </div>
            <button type="button" class="plan__boton">Obtener Premium</button>
         </div>
    </div>

    <p class="panel-premium__mensaje" id="mensajePago" aria-live="polite">&nbsp;</p>

    <p class="panel-premium__letra-chica">
        Los pagos son procesados por el Departamento de Cobros Imaginarios de la Biblioteca.<br>
        Oferta válida hasta que el programador termine el proyecto.
    </p>

    <a href="index.php?c=home&a=inicio" class="panel-premium__volver">Ir al inicio</a>
</div>

<script>
(function () {
    var ficha = document.querySelector('.ficha-resultado');
    var panel = document.getElementById('panelPremium');
    var mensaje = document.getElementById('mensajePago');

    var frasesError = [
        'Error 402: todavía no sabemos cómo cobrarte.',
        'El dinero ha sido enviado. Lamentablemente, a otra biblioteca.',
        'Tu tarjeta fue rechazada por falta de existencia.'
    ];

    document.querySelectorAll('.plan__boton').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var frase = frasesError[Math.floor(Math.random() * frasesError.length)];
            mensaje.textContent = frase;
            mensaje.classList.remove('mostrar');
            void mensaje.offsetWidth;
            mensaje.classList.add('mostrar');
        });
    });

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function mostrarPanel() {
        if (ficha) {
            ficha.style.display = 'none';
        }
        panel.classList.remove('oculto');
        void panel.offsetWidth;
        panel.classList.add('visible');
    }

    setTimeout(function () {
        if (!ficha || reduceMotion) {
            mostrarPanel();
            return;
        }

        ficha.style.animation = 'none';
        ficha.classList.add('saliendo');

        setTimeout(mostrarPanel, 450);
    }, 2500);
})();
</script>

</div>

<?php include dirname(__DIR__) . '/layouts/footer.php'; ?>
</body>
</html>
