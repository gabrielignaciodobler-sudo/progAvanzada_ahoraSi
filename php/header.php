<header>
    <p>Sistema de gestión de biblioteca</p>
    <img src="img/logo.png" class="img-fluid" alt="Escudo de la biblioteca">
    <?php if (!empty($_SESSION['logueado']) && $_SESSION['logueado'] === true): ?>
        <div class="sesion-activa">
            <span>Logueado como <?php echo htmlspecialchars($_SESSION['usuario'] ?? 'usuario', ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($_SESSION['rol'] ?? 'lector', ENT_QUOTES, 'UTF-8'); ?>)</span>
            <a href="libros.php" class="btn-login btn-login--compact">Catálogo</a>
            <a href="logout.php" class="btn-login btn-login--compact">Cerrar sesión</a>
        </div>
    <?php endif; ?>
    <hr>
</header>