<?php

// Modelo de acceso a datos del "Producto" del ABM (tabla `productos`)

declare(strict_types=1);

class Libro
{
    private PDO $pdo;

    /* Categorías permitidas en el ABM */
    public const CATEGORIAS = ['Novela', 'Cuentos', 'Poesía', 'Ensayo', 'Académico', 'Infantil'];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Lista los libros. Por defecto solo los activos (no dados de baja)
  
    public function listar(bool $incluirInactivos = false, string $busqueda = ''): array
    {
        $sql = 'SELECT id, nombre, autor, descripcion, precio, stock, categoria, activo, creado_en
                  FROM productos';

        $condiciones = [];
        $parametros  = [];

        if (!$incluirInactivos) {
            $condiciones[] = 'activo = 1';
        }

        if ($busqueda !== '') {

            /* Busca por título o autor. Se usan DOS parámetros distintos porque
             las sentencias preparadas nativas de MySQL no permiten reutilizar
             el mismo marcador (:param) más de una vez */
            $condiciones[] = '(nombre LIKE :busqueda_nombre OR autor LIKE :busqueda_autor)';
            $parametros[':busqueda_nombre'] = '%' . $busqueda . '%';
            $parametros[':busqueda_autor']  = '%' . $busqueda . '%';
        }

        if ($condiciones !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $condiciones);
        }

        $sql .= ' ORDER BY nombre ASC';

        $sentencia = $this->pdo->prepare($sql);
        foreach ($parametros as $clave => $valor) {
            $sentencia->bindValue($clave, $valor, PDO::PARAM_STR);
        }
        $sentencia->execute();

        return $sentencia->fetchAll();
    }

    // Obtiene un libro por su ID (activo o no)
 
    public function buscarPorId(int $id): ?array
    {
        $sentencia = $this->pdo->prepare(
            'SELECT id, nombre, autor, descripcion, precio, stock, categoria, activo, creado_en
               FROM productos
              WHERE id = :id
              LIMIT 1'
        );
        $sentencia->bindValue(':id', $id, PDO::PARAM_INT);
        $sentencia->execute();

        $fila = $sentencia->fetch();

        return $fila === false ? null : $fila;
    }

    // Inserta un nuevo libro y devuelve el ID generado
    
    public function crear(array $datos): int
    {
        $sql = 'INSERT INTO productos (nombre, autor, descripcion, precio, stock, categoria)
                VALUES (:nombre, :autor, :descripcion, :precio, :stock, :categoria)';

        $sentencia = $this->pdo->prepare($sql);
        $sentencia->bindValue(':nombre',      (string) $datos['nombre'],            PDO::PARAM_STR);
        $sentencia->bindValue(':autor',       (string) $datos['autor'],            PDO::PARAM_STR);
        $sentencia->bindValue(':descripcion', (string) $datos['descripcion'],      PDO::PARAM_STR);
        $sentencia->bindValue(':precio',      (string) $datos['precio']);          // DECIMAL se envía como string
        $sentencia->bindValue(':stock',       (int) $datos['stock'],               PDO::PARAM_INT);
        $sentencia->bindValue(':categoria',   (string) $datos['categoria'],        PDO::PARAM_STR);
        $sentencia->execute();

        return (int) $this->pdo->lastInsertId();
    }

    // Actualiza un libro existente, devuelve true si modificó alguna fila
 
    public function actualizar(int $id, array $datos): bool
    {
        $sql = 'UPDATE productos
                   SET nombre      = :nombre,
                       autor       = :autor,
                       descripcion = :descripcion,
                       precio      = :precio,
                       stock       = :stock,
                       categoria   = :categoria
                 WHERE id = :id';

        $sentencia = $this->pdo->prepare($sql);
        $sentencia->bindValue(':nombre',      (string) $datos['nombre'],       PDO::PARAM_STR);
        $sentencia->bindValue(':autor',       (string) $datos['autor'],       PDO::PARAM_STR);
        $sentencia->bindValue(':descripcion', (string) $datos['descripcion'], PDO::PARAM_STR);
        $sentencia->bindValue(':precio',      (string) $datos['precio']);
        $sentencia->bindValue(':stock',       (int) $datos['stock'],          PDO::PARAM_INT);
        $sentencia->bindValue(':categoria',   (string) $datos['categoria'],   PDO::PARAM_STR);
        $sentencia->bindValue(':id',          $id,                             PDO::PARAM_INT);
        $sentencia->execute();

        return $sentencia->rowCount() >= 0; // rowCount 0 puede significar "sin cambios"
    }

    /* Baja LÓGICA: marca el libro como inactivo (activo = 0)
     Se conserva el registro histórico en la base de datos */

    public function eliminar(int $id): bool
    {
        $sentencia = $this->pdo->prepare('UPDATE productos SET activo = 0 WHERE id = :id');
        $sentencia->bindValue(':id', $id, PDO::PARAM_INT);
        $sentencia->execute();

        return $sentencia->rowCount() > 0;
    }

    // Reactiva un libro dado de baja (activo = 1)

    public function restaurar(int $id): bool
    {
        $sentencia = $this->pdo->prepare('UPDATE productos SET activo = 1 WHERE id = :id');
        $sentencia->bindValue(':id', $id, PDO::PARAM_INT);
        $sentencia->execute();

        return $sentencia->rowCount() > 0;
    }

    // Validacion de los datos de entrada
  
    // Devuelve un arreglo de errores (vacío si todo es válido)
 
    public static function validar(array $datos): array
    {
        $errores = [];

        $nombre = trim((string) ($datos['nombre'] ?? ''));
        if ($nombre === '') {
            $errores['nombre'] = 'El título del libro es obligatorio.';
        } elseif (mb_strlen($nombre) > 150) {
            $errores['nombre'] = 'El título no puede superar los 150 caracteres.';
        }

        $autor = trim((string) ($datos['autor'] ?? ''));
        if ($autor === '') {
            $errores['autor'] = 'El autor es obligatorio.';
        } elseif (mb_strlen($autor) > 120) {
            $errores['autor'] = 'El autor no puede superar los 120 caracteres.';
        }

        $categoria = trim((string) ($datos['categoria'] ?? ''));
        if ($categoria === '' || !in_array($categoria, self::CATEGORIAS, true)) {
            $errores['categoria'] = 'Debe seleccionar una categoría válida.';
        }

        $precio = $datos['precio'] ?? '';
        if ($precio === '' || !is_numeric($precio) || (float) $precio < 0) {
            $errores['precio'] = 'El precio debe ser un número mayor o igual a 0.';
        }

        $stock = $datos['stock'] ?? '';
        if ($stock === '' || filter_var($stock, FILTER_VALIDATE_INT) === false || (int) $stock < 0) {
            $errores['stock'] = 'El stock debe ser un número entero mayor o igual a 0.';
        }

        return $errores;
    }

    // Normaliza/limpia los datos recibidos del formulario antes de persistir

  static function normalizar(array $datos): array
    {
        return [
            'nombre'      => trim((string) ($datos['nombre'] ?? '')),
            'autor'       => trim((string) ($datos['autor'] ?? '')),
            'descripcion' => trim((string) ($datos['descripcion'] ?? '')),
            'precio'      => number_format((float) ($datos['precio'] ?? 0), 2, '.', ''),
            'stock'       => (int) ($datos['stock'] ?? 0),
            'categoria'   => trim((string) ($datos['categoria'] ?? '')),
        ];
    }
}