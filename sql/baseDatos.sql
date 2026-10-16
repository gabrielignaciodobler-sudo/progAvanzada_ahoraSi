-- Selecciona primero la base de datos destino en phpMyAdmin.
-- Este archivo solo crea las tablas de la aplicación.
-- No crea ni elimina bases de datos y no inserta datos de demostración.

CREATE TABLE usuarios (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario     VARCHAR(50)  NOT NULL,
    password    VARCHAR(255) NOT NULL,
    rol         ENUM('admin','bibliotecario','lector') NOT NULL DEFAULT 'lector',
    activo      TINYINT(1)   NOT NULL DEFAULT 1,
    creado_en   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_usuario (usuario)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE productos (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre       VARCHAR(150)  NOT NULL,
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
