<?php
/**
 * ============================================================================
 *  controladores/HomeControlador.php
 *  Controlador para las pantallas de bienvenida y panel principal.
 * ============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/Controlador.php';
require_once dirname(__DIR__) . '/php/auth.php';

class HomeControlador extends Controlador
{
    /**
     * Portada pública de la biblioteca.
     */
    public function index(): void
    {
        if (usuarioLogueado()) {
            $this->redirigir('index.php?c=home&a=inicio');
        }

        $this->renderizar('home/index');
    }

    /**
     * Panel inicial tras haber iniciado sesión.
     */
    public function inicio(): void
    {
        requerirLogin();

        $usuario        = usuarioActual();
        $rol            = rolActual();
        $puedeGestionar = puedeGestionarLibros();

        $this->renderizar('home/inicio', [
            'usuario'        => $usuario,
            'rol'            => $rol,
            'puedeGestionar' => $puedeGestionar,
        ]);
    }
}

