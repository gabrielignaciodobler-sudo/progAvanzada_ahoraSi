<?php
/**
 * ============================================================================
 *  app/Modelos/Usuario.php
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

    /** El registro público solo puede crear cuentas lectoras. */
    public const ROLES_REGISTRO = ['lector'];
    public const ROLES_CUENTA = ['admin', 'bibliotecario', 'lector'];

    /**
     * Comprueba si ya existe un nombre de usuario (útil para altas de usuarios).
     */
    public function existeUsuario(string $usuario, int $excluirId = 0): bool
    {
        $sql = 'SELECT 1 FROM usuarios WHERE usuario = :usuario';
        if ($excluirId > 0) {
            $sql .= ' AND id <> :excluir_id';
        }
        $sql .= ' LIMIT 1';

        $sentencia = $this->pdo->prepare($sql);
        $sentencia->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        if ($excluirId > 0) {
            $sentencia->bindValue(':excluir_id', $excluirId, PDO::PARAM_INT);
        }
        $sentencia->execute();

        return $sentencia->fetchColumn() !== false;
    }

    /**
     * Lista cuentas sin exponer sus hashes de contraseña.
     *
     * @return array<int,array<string,mixed>>
     */
    public function listarCuentas(): array
    {
        $sentencia = $this->pdo->query(
            'SELECT id, usuario, rol, activo, creado_en
               FROM usuarios
              ORDER BY usuario ASC'
        );

        return $sentencia->fetchAll();
    }

    /**
     * Busca una cuenta sin exponer su hash de contraseña.
     *
     * @return array<string,mixed>|null
     */
    public function buscarCuentaPorId(int $id): ?array
    {
        $sentencia = $this->pdo->prepare(
            'SELECT id, usuario, rol, activo, creado_en
               FROM usuarios
              WHERE id = :id
              LIMIT 1'
        );
        $sentencia->bindValue(':id', $id, PDO::PARAM_INT);
        $sentencia->execute();

        $fila = $sentencia->fetch();

        return $fila === false ? null : $fila;
    }

    /** Crea una cuenta desde el panel administrativo. */
    public function crearCuenta(string $usuario, string $password, string $rol): int
    {
        if (!in_array($rol, self::ROLES_CUENTA, true)) {
            throw new InvalidArgumentException('El rol seleccionado no es válido.');
        }

        $sentencia = $this->pdo->prepare(
            'INSERT INTO usuarios (usuario, password, rol, activo)
             VALUES (:usuario, :password, :rol, 1)'
        );
        $sentencia->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $sentencia->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
        $sentencia->bindValue(':rol', $rol, PDO::PARAM_STR);
        $sentencia->execute();

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Actualiza datos de cuenta y cambia la contraseña solo si se indicó una.
     *
     * @throws DomainException si la modificación desactivaría al último administrador activo.
     */
    public function actualizarCuenta(int $id, string $usuario, string $password, string $rol): bool
    {
        if (!in_array($rol, self::ROLES_CUENTA, true)) {
            throw new InvalidArgumentException('El rol seleccionado no es válido.');
        }

        $this->pdo->beginTransaction();

        try {
            $administradoresActivos = $this->bloquearAdministradoresActivos();
            $actual = $this->buscarCuentaPorIdBloqueada($id);

            if ($actual === null) {
                $this->pdo->commit();
                return false;
            }

            if ((int) $actual['activo'] === 1 && $actual['rol'] === 'admin' && $rol !== 'admin'
                && count($administradoresActivos) <= 1) {
                throw new DomainException('No se puede quitar el rol de administrador a la última cuenta administradora activa.');
            }

            $sql = 'UPDATE usuarios SET usuario = :usuario, rol = :rol';
            if ($password !== '') {
                $sql .= ', password = :password';
            }
            $sql .= ' WHERE id = :id';

            $sentencia = $this->pdo->prepare($sql);
            $sentencia->bindValue(':usuario', $usuario, PDO::PARAM_STR);
            $sentencia->bindValue(':rol', $rol, PDO::PARAM_STR);
            if ($password !== '') {
                $sentencia->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
            }
            $sentencia->bindValue(':id', $id, PDO::PARAM_INT);
            $sentencia->execute();
            $this->pdo->commit();

            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Activa o desactiva una cuenta preservando la última cuenta administradora activa.
     *
     * @throws DomainException si se intenta desactivar al último administrador activo.
     */
    public function establecerCuentaActiva(int $id, bool $activa): bool
    {
        $this->pdo->beginTransaction();

        try {
            $administradoresActivos = $this->bloquearAdministradoresActivos();
            $actual = $this->buscarCuentaPorIdBloqueada($id);

            if ($actual === null) {
                $this->pdo->commit();
                return false;
            }

            if (!$activa && (int) $actual['activo'] === 1 && $actual['rol'] === 'admin'
                && count($administradoresActivos) <= 1) {
                throw new DomainException('No se puede desactivar la última cuenta administradora activa.');
            }

            $sentencia = $this->pdo->prepare('UPDATE usuarios SET activo = :activo WHERE id = :id');
            $sentencia->bindValue(':activo', $activa ? 1 : 0, PDO::PARAM_INT);
            $sentencia->bindValue(':id', $id, PDO::PARAM_INT);
            $sentencia->execute();
            $this->pdo->commit();

            return $sentencia->rowCount() > 0;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Bloquea las filas administradoras activas para serializar cambios de rol y estado.
     *
     * @return array<int,array<string,mixed>>
     */
    private function bloquearAdministradoresActivos(): array
    {
        $sentencia = $this->pdo->query(
            "SELECT id FROM usuarios WHERE rol = 'admin' AND activo = 1 FOR UPDATE"
        );

        return $sentencia->fetchAll();
    }

    /** @return array<string,mixed>|null */
    private function buscarCuentaPorIdBloqueada(int $id): ?array
    {
        $sentencia = $this->pdo->prepare(
            'SELECT id, usuario, rol, activo
               FROM usuarios
              WHERE id = :id
              LIMIT 1
              FOR UPDATE'
        );
        $sentencia->bindValue(':id', $id, PDO::PARAM_INT);
        $sentencia->execute();

        $fila = $sentencia->fetch();

        return $fila === false ? null : $fila;
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
