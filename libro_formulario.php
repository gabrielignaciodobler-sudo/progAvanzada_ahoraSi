<?php
/**
 * ============================================================================
 *  libro_formulario.php
 *  Formulario único para el ALTA y la MODIFICACIÓN de un libro.
 *    - Sin parámetro ?id  -> Alta.
 *    - Con parámetro ?id   -> Modificación (carga los datos actuales).
 * ============================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/modelos/Libro.php';

requerirLogin();
requerirPermisoGestion();

$id       = (int) ($_GET['id'] ?? 0);
$esEdicion = $id > 0;

/* Valores por defecto para el alta. */
$libro = [
    'id'          => 0,
    'nombre'      => '',
    'autor'       => '',
    'descripcion' => '',
    'precio'      => '',
    'stock'       => '',
    'categoria'   => '',
];

/* Modo edición: recuperar el registro desde la BD. */
if ($esEdicion) {
    try {
        $pdo    = conectarBD();
        $modelo = new Libro($pdo);
        $registro = $modelo->buscarPorId($id);

        if ($registro === null) {
            setFlash('error', 'El libro solicitado no existe.');
            header('Location: libros.php');
            exit;
        }

        $libro = [
            'id'          => (int) $registro['id'],
            'nombre'      => $registro['nombre'],
            'autor'       => $registro['autor'],
            'descripcion' => $registro['descripcion'],
            'precio'      => $registro['precio'],
            'stock'       => $registro['stock'],
            'categoria'   => $registro['categoria'],
        ];
    } catch (PDOException $e) {
        error_log('[Libro formulario] Error de consulta: ' . $e->getMessage());
        $errorPagina = MENSAJE_ERROR_CONEXION;
    }
}

/* Si volvemos de un intento fallido, recuperamos lo cargado y los errores. */
$errores = $_SESSION['form_errores'] ?? [];
$datos   = $_SESSION['form_datos'] ?? null;
unset($_SESSION['form_errores'], $_SESSION['form_datos']);

if (is_array($datos)) {
    $libro = array_merge($libro, $datos);
}

/** Atajo para pintar un mensaje de error por campo. */
function errorCampo(array $errores, string $campo): string
{
    return isset($errores[$campo])
        ? '<span class="error-campo">' . escapar($errores[$campo]) . '</span>'
        : '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Biblioteca - <?php echo $esEdicion ? 'Editar libro' : 'Nuevo libro'; ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=<?php echo filemtime(__DIR__ . '/css/style.css'); ?>">
</head>
<body>
<?php include __DIR__ . '/php/header.php'; ?>
<main>
  <section class="abm abm--form">

    <div class="abm__barra">
      <div>
        <h1 class="abm__titulo"><?php echo $esEdicion ? 'Editar libro' : 'Nuevo libro'; ?></h1>
        <p class="abm__subtitulo"><?php echo $esEdicion ? 'Modificación de un registro existente' : 'Alta de un nuevo registro'; ?></p>
      </div>
      <a href="libros.php" class="btn-login btn-login--compact">&larr; Volver al catálogo</a>
    </div>

    <?php if (!empty($errorPagina)) : ?>
      <p class="mensaje mensaje--error"><?php echo escapar($errorPagina); ?></p>
    <?php endif; ?>

    <form action="libro_guardar.php" method="post" class="form-abm" novalidate>
      <?php echo campoCsrf(); ?>
      <input type="hidden" name="id" value="<?php echo (int) $libro['id']; ?>">

      <div class="campo">
        <label for="nombre">Título <span class="req">*</span></label>
        <input type="text" id="nombre" name="nombre" maxlength="150" required
               value="<?php echo escapar($libro['nombre']); ?>">
        <?php echo errorCampo($errores, 'nombre'); ?>
      </div>

      <div class="campo">
        <label for="autor">Autor <span class="req">*</span></label>
        <input type="text" id="autor" name="autor" maxlength="120" required
               value="<?php echo escapar($libro['autor']); ?>">
        <?php echo errorCampo($errores, 'autor'); ?>
      </div>

      <div class="campo-doble">
        <div class="campo">
          <label for="categoria">Categoría <span class="req">*</span></label>
          <select id="categoria" name="categoria" required>
            <option value="">Seleccionar…</option>
            <?php foreach (Libro::CATEGORIAS as $categoria) : ?>
              <option value="<?php echo escapar($categoria); ?>"
                <?php echo $libro['categoria'] === $categoria ? 'selected' : ''; ?>>
                <?php echo escapar($categoria); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php echo errorCampo($errores, 'categoria'); ?>
        </div>

        <div class="campo">
          <label for="precio">Precio ($) <span class="req">*</span></label>
          <input type="number" id="precio" name="precio" min="0" step="0.01" required
                 value="<?php echo escapar((string) $libro['precio']); ?>">
          <?php echo errorCampo($errores, 'precio'); ?>
        </div>

        <div class="campo">
          <label for="stock">Stock <span class="req">*</span></label>
          <input type="number" id="stock" name="stock" min="0" step="1" required
                 value="<?php echo escapar((string) $libro['stock']); ?>">
          <?php echo errorCampo($errores, 'stock'); ?>
        </div>
      </div>

      <div class="campo">
        <label for="descripcion">Descripción</label>
        <textarea id="descripcion" name="descripcion" rows="3"><?php echo escapar($libro['descripcion']); ?></textarea>
      </div>

      <div class="form-abm__acciones">
        <button type="submit"><?php echo $esEdicion ? 'Guardar cambios' : 'Dar de alta'; ?></button>
        <a href="libros.php" class="btn-cancelar">Cancelar</a>
      </div>
    </form>

  </section>
</main>
<?php include __DIR__ . '/php/footer.php'; ?>
</body>
</html>
