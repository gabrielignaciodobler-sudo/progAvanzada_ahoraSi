-- ============================================================================
--  Sistema de Biblioteca - Tercera Entrega
--  SGBD: MySQL / MariaDB
--  Contenido: DDL (estructura) + DML (datos de prueba)
--  Ejecutar con:  mysql -u root -p < sql/biblioteca.sql
--  o pegando el contenido en phpMyAdmin / consola de MySQL.
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 1) Creación de la base de datos
-- ----------------------------------------------------------------------------
SET NAMES utf8mb4;

DROP DATABASE IF EXISTS biblioteca;

CREATE DATABASE biblioteca
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE biblioteca;

-- ----------------------------------------------------------------------------
-- 2) Tabla `usuarios`: credenciales y rol para el Login
--    La contraseña se guarda HASHEADA (bcrypt) con password_hash() de PHP,
--    nunca en texto plano.
-- ----------------------------------------------------------------------------
CREATE TABLE usuarios (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario     VARCHAR(50)  NOT NULL,
    password    VARCHAR(255) NOT NULL,                 -- hash bcrypt
    rol         ENUM('admin','bibliotecario','lector') NOT NULL DEFAULT 'lector',
    activo      TINYINT(1)   NOT NULL DEFAULT 1,
    creado_en   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_usuario (usuario)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3) Tabla `productos`: en este sistema el "producto" del ABM es un LIBRO.
--    La columna `activo` habilita la BAJA LÓGICA (no se borra el registro).
-- ----------------------------------------------------------------------------
CREATE TABLE productos (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre       VARCHAR(150)  NOT NULL,               -- título del libro
    autor        VARCHAR(120)  NOT NULL DEFAULT '',
    descripcion  TEXT          NULL,
    precio       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock        INT           NOT NULL DEFAULT 0,
    categoria    VARCHAR(80)   NOT NULL DEFAULT '',
    activo       TINYINT(1)    NOT NULL DEFAULT 1,
    creado_en    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_productos_nombre    (nombre),
    KEY idx_productos_categoria (categoria)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 4) DML - Usuarios de prueba
--    IMPORTANTE: los valores de `password` ya están hasheados con bcrypt.
--    Credenciales en texto plano (para la defensa del TP):
--        fcytuader -> programacionavanzada
--        admin     -> admin
--    Si se quiere regenerar un hash distinto, ejecutar en PHP:
--        echo password_hash('miClave', PASSWORD_DEFAULT);
-- ----------------------------------------------------------------------------
INSERT INTO usuarios (usuario, password, rol) VALUES
    ('fcytuader', '$2y$10$Y9TNll.Bv0zlfaJmrLSOjOeQMSyvRh9JDIXK98aV0i5GeCnhNi9L2', 'bibliotecario'),
    ('admin',     '$2y$10$HHJT7Uy9lZ2hySFlpr2/Ou0j0NxB27VLDWOlrvpFJRCyvFR4pWCYi', 'admin');

-- ----------------------------------------------------------------------------
-- 5) DML - Productos (libros) de prueba
-- ----------------------------------------------------------------------------
INSERT INTO productos (nombre, autor, descripcion, precio, stock, categoria) VALUES
    ('Cien años de soledad', 'Gabriel García Márquez', 'Novela cumbre del realismo mágico latinoamericano.', 12500.00, 8,  'Novela'),
    ('El Aleph',             'Jorge Luis Borges',       'Colección de cuentos fantásticos y filosóficos.',      9800.00,  5,  'Cuentos'),
    ('Rayuela',              'Julio Cortázar',          'Novela experimental de lectura múltiple.',             14200.00, 3,  'Novela'),
    ('Programación Avanzada','Ian Sommerville',         'Introducción a la ingeniería de software moderna.',    18900.00, 10, 'Académico'),
    ('La invención de Morel', 'Adolfo Bioy Casares',    'Novela de anticipación y misterio.',                    7600.00, 6,  'Novela');

-- ----------------------------------------------------------------------------
-- 6) Comprobación rápida
-- ----------------------------------------------------------------------------
SELECT id, usuario, rol, activo FROM usuarios;
SELECT id, nombre, autor, precio, stock, categoria, activo FROM productos;
