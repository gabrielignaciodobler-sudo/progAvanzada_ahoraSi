<?php
/**
 * ============================================================================
 *  modelos/Usuario.php
 *  Modelo de acceso a datos para la autenticación (tabla `usuarios`).
 *  Todas las consultas son PREPARADAS (parametrizadas) para evitar
 *  Inyección SQL.
 * ============================================================================
 */

declare(strict_types=1);

class Usuario
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Valida las credenciales contra la base de datos.
     *
     * @return array<string,mixed>|null Datos del usuario (sin el hash) si las
     *                                  credenciales son correctas; null en caso contrario.
     */
    public function validarCredenciales(string $usuario, string $password): ?array
    {
        $sql = 'SELECT id, usuario, password, rol, activo
                  FROM usuarios
                 WHERE usuario = :usuario
                 LIMIT 1';

        $sentencia = $this->pdo->prepare($sql);
        $sentencia->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $sentencia->execute();

        $fila = $sentencia->fetch();

        // Usuario inexistente o inactivo.
        if ($fila === false || (int) $fila['activo'] !== 1) {
            return null;
        }

        // Verificación del hash bcrypt almacenado.
        if (!password_verify($password, (string) $fila['password'])) {
            return null;
        }

        // Nunca propagar el hash fuera del modelo.
        unset($fila['password']);

        return $fila;
    }

    /** Roles permitidos en el registro de usuarios. */
    public const ROLES_REGISTRO = ['lector', 'bibliotecario'];

    /**
     * Comprueba si ya existe un nombre de usuario (útil para altas de usuarios).
     */
    public function existeUsuario(string $usuario): bool
    {
        $sentencia = $this->pdo->prepare('SELECT 1 FROM usuarios WHERE usuario = :usuario LIMIT 1');
        $sentencia->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $sentencia->execute();

        return $sentencia->fetchColumn() !== false;
    }

    /**
     * Registra un nuevo usuario en la base de datos con la contraseña hasheada (bcrypt).
     *
     * @param string $usuario  Nombre de usuario.
     * @param string $password Contraseña en texto plano.
     * @param string $rol      Rol ('lector' o 'bibliotecario').
     * @return int ID del nuevo usuario insertado.
     * @throws InvalidArgumentException Si el rol no es válido.
     * @throws RuntimeException Si el usuario ya existe.
     */
    public function registrar(string $usuario, string $password, string $rol = 'lector'): int
    {
        if (!in_array($rol, self::ROLES_REGISTRO, true)) {
            throw new InvalidArgumentException("El rol '{$rol}' no está permitido.");
        }

        if ($this->existeUsuario($usuario)) {
            throw new RuntimeException('El nombre de usuario ya se encuentra registrado.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = 'INSERT INTO usuarios (usuario, password, rol, activo)
                VALUES (:usuario, :password, :rol, 1)';

        $sentencia = $this->pdo->prepare($sql);
        $sentencia->bindValue(':usuario',  $usuario, PDO::PARAM_STR);
        $sentencia->bindValue(':password', $hash,    PDO::PARAM_STR);
        $sentencia->bindValue(':rol',      $rol,     PDO::PARAM_STR);
        $sentencia->execute();

        return (int) $this->pdo->lastInsertId();
    }
}
