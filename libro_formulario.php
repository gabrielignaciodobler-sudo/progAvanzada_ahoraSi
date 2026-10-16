<?php
/**
 * Puente de compatibilidad hacia la arquitectura MVC.
 */
declare(strict_types=1);

require_once __DIR__ . '/controladores/LibroControlador.php';

$controlador = new LibroControlador();
if (isset($_GET['id']) && (int)$_GET['id'] > 0) {
    $controlador->editar();
} else {
    $controlador->crear();
}
