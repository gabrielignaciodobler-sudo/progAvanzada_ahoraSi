<?php
/**
 * ============================================================================
 *  controladores/Controlador.php
 *  Clase base abstracta para todos los controladores del sistema.
 * ============================================================================
 */

declare(strict_types=1);

abstract class Controlador
{
    /**
     * Renderiza una vista y le pasa los datos suministrados.
     *
     * @param string $vista Ruta de la vista relativa a la carpeta `vistas/` (sin extensión .php).
     * @param array<string,mixed> $datos Parámetros que estarán disponibles como variables locales en la vista.
     */
    protected function renderizar(string $vista, array $datos = []): void
    {
        // Extrae las variables para que la vista las consuma de forma directa
        extract($datos, EXTR_SKIP);

        $archivoVista = dirname(__DIR__) . '/vistas/' . $vista . '.php';

        if (!file_exists($archivoVista)) {
            http_response_code(404);
            die("Error 404: La vista «{$vista}» no fue encontrada.");
        }

        require $archivoVista;
    }

    /**
     * Redirige al navegador a una URL dada y finaliza la ejecución.
     */
    protected function redirigir(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
}

