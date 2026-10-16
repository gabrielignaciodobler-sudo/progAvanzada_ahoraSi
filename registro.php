<?php
/**
 * Puente de compatibilidad hacia la arquitectura MVC.
 */
declare(strict_types=1);

require_once __DIR__ . '/controladores/AuthControlador.php';

$controlador = new AuthControlador();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $controlador->registro();
} else {
    $controlador->mostrarRegistro();
}
