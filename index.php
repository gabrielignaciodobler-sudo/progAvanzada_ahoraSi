<?php

/* Punto de entrada único de la aplicación bajo el patrón MVC.
 Recibe los parámetros `c` (controlador) y `a` (acción) por querystring */

declare(strict_types=1);

spl_autoload_register(static function (string $clase): void {
    foreach ([__DIR__ . '/app/Controladores', __DIR__ . '/app/Modelos'] as $directorio) {
        $archivo = $directorio . DIRECTORY_SEPARATOR . $clase . '.php';

        if (is_file($archivo)) {
            require_once $archivo;
            return;
        }
    }
});

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/Helpers/auth.php';

iniciarSesion();

// Resolución de controlador y acción con valores por defecto

$controladorNombre = strtolower((string) ($_GET['c'] ?? $_GET['controlador'] ?? 'home'));
$accionNombre      = (string) ($_GET['a'] ?? $_GET['accion'] ?? '');

// Mapa de controladores disponibles

$controladores = [
    'home'   => 'HomeControlador',
    'auth'   => 'AuthControlador',
    'libro'  => 'LibroControlador',
    'libros' => 'LibroControlador',
    'usuario' => 'UsuarioControlador',
    'usuarios' => 'UsuarioControlador',
];

if (!isset($controladores[$controladorNombre])) {
    http_response_code(404);
    die("Error 404: El controlador «{$controladorNombre}» no existe.");
}

$claseControlador   = $controladores[$controladorNombre];
if (!class_exists($claseControlador)) {
    http_response_code(500);
    die("Error 500: No se encuentra el archivo del controlador «{$claseControlador}».");
}

$instancia = new $claseControlador();

// Si no se especificó acción, resolvemos la acción predeterminada según el controlador

if ($accionNombre === '') {
    if ($controladorNombre === 'auth') {
        $accionNombre = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' ? 'login' : 'mostrarLogin';
    } else {
        $accionNombre = 'index';
    }
}

// Mapeos intuitivos de acciones (alias amigables)

$aliasAcciones = [
    'mostrarlogin'    => 'mostrarLogin',
    'login'           => ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' ? 'login' : 'mostrarLogin',
    'mostrarregistro' => 'mostrarRegistro',
    'registro'        => ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' ? 'registro' : 'mostrarRegistro',
];

$accionMetodo = $aliasAcciones[strtolower($accionNombre)] ?? $accionNombre;

if (!method_exists($instancia, $accionMetodo) || !is_callable([$instancia, $accionMetodo])) {
    http_response_code(404);
    die("Error 404: La acción «{$accionNombre}» no existe en «{$claseControlador}».");
}

// Ejecución del controlador y acción

$instancia->$accionMetodo();