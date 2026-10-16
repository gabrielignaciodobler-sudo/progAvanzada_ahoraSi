<?php
/**
 * Puente de compatibilidad hacia la arquitectura MVC.
 */
declare(strict_types=1);

require_once __DIR__ . '/controladores/LibroControlador.php';

(new LibroControlador())->guardar();
