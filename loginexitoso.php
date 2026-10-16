<?php
session_start();

if (empty($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header('Location: login.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Biblioteca</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=<?php echo filemtime(__DIR__ . '/css/style.css'); ?>">
</head>
<body class="auth-page">

<div class="resultado">

<div class="ficha-resultado">
    <span class="sello sello--ok">Acceso concedido</span>
    <h2>Buenos dias, <?php echo htmlspecialchars($_SESSION['usuario'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h2>
    <p>Te logueaste correctamente!</p>
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

    <a href="login.php" class="panel-premium__volver">Ir al inicio</a>
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
            void mensaje.offsetWidth; // reflow para poder repetir la animación
            mensaje.classList.add('mostrar');
        });
    });

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function mostrarPanel() {
        if (ficha) {
            ficha.style.display = 'none';
        }
        panel.classList.remove('oculto');
        void panel.offsetWidth; // reflow antes de animar la entrada
        panel.classList.add('visible');
    }

    // Espera a que el sello termine de mostrarse y el usuario
    // alcance a leer el mensaje de bienvenida antes de pasar de etapa.
    setTimeout(function () {
        if (!ficha || reduceMotion) {
            mostrarPanel();
            return;
        }

        // Liberamos la animación de entrada (fichaEntra) antes de
        // iniciar la transición de salida, para que ambas no compitan
        // por la misma propiedad y la transición se dispare siempre.
        ficha.style.animation = 'none';
        ficha.classList.add('saliendo');

        // Tiempo fijo en vez de esperar 'transitionend': así la etapa 2
        // aparece siempre, incluso si por algún motivo el navegador
        // no dispara ese evento.
        setTimeout(mostrarPanel, 450);
    }, 2500);
})();
</script>

</div>

<?php include("php/footer.php"); ?>
</body>
</html>