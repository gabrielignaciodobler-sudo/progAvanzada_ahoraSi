<?php
/**
 * Puente de compatibilidad hacia la arquitectura MVC.
 */
declare(strict_types=1);

require_once __DIR__ . '/controladores/AuthControlador.php';

(new AuthControlador())->logout();
