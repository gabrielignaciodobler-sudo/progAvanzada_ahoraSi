<header>
    <p>Sistema de gestión de biblioteca</p>
    <img src="assets/img/logo.png" class="img-fluid" alt="Escudo de la biblioteca">
    <?php if (usuarioLogueado()): ?>
        <div class="sesion-activa">
            <span>Logueado como <?php echo escapar(usuarioActual()); ?> (<?php echo escapar(rolActual() ?? 'lector'); ?>)</span>
            <a href="index.php?c=libro&a=index" class="btn-login btn-login--compact">Catálogo</a>
            <?php if (rolActual() === 'admin'): ?>
                <a href="index.php?c=usuario" class="btn-login btn-login--compact">Cuentas</a>
            <?php endif; ?>
            <a href="index.php?c=auth&a=logout" class="btn-login btn-login--compact">Cerrar sesión</a>
        </div>
    <?php endif; ?>
    <hr>
</header>
