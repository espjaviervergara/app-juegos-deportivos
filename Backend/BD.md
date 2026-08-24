# Base de Datos — App Juegos Deportivos

> MySQL / MariaDB (Laragon). InnoDB + utf8mb4_unicode_ci. Sin Redis/queue. Migrable via `php migrate.php`.
> Orden de creación respeta FKs. Todo con `IF NOT EXISTS` y `prepared statements` en PHP.

## Diagrama resumido

```
deportes 1—N torneos N—M equipos 1—N jugadores
              1—N jornadas 1—N partidos 0—1 resultados_propuestos
              1—N usuario_torneo (editor ↔ torneo)
usuarios 1—N refresh_tokens, audit_log, rate_limits
```

## Script DDL completo

```sql
-- BD.md — DDL para app_juegos_deportivos
-- Ejecutar en orden. Compatible MySQL 5.7+ / MariaDB 10+

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

-- 1) Deportes (catálogo)
CREATE TABLE IF NOT EXISTS deportes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL UNIQUE,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Usuarios (admin_principal, editor)
CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  email VARCHAR(180) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  rol ENUM('admin','editor') NOT NULL DEFAULT 'editor',
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_usuarios_rol (rol),
  INDEX idx_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Torneos
CREATE TABLE IF NOT EXISTS torneos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  deporte_id INT UNSIGNED NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  categoria ENUM('M','F','Mixto') NOT NULL,
  formato ENUM('liga','eliminatoria','grupos+eliminatoria') NOT NULL,
  estado ENUM('borrador','activo','finalizado','archivado') NOT NULL DEFAULT 'activo',
  fecha_inicio DATE NULL,
  fecha_fin DATE NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_torneos_deporte FOREIGN KEY (deporte_id) REFERENCES deportes(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_torneos_deporte (deporte_id),
  INDEX idx_torneos_categoria (categoria),
  INDEX idx_torneos_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4) Equipos
CREATE TABLE IF NOT EXISTS equipos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  escudo_path VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_equipos_nombre (nombre),
  INDEX idx_equipos_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) Torneo <-> Equipo (inscripción aislada por torneo)
CREATE TABLE IF NOT EXISTS torneo_equipo (
  torneo_id INT UNSIGNED NOT NULL,
  equipo_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (torneo_id, equipo_id),
  CONSTRAINT fk_te_torneo FOREIGN KEY (torneo_id) REFERENCES torneos(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_te_equipo FOREIGN KEY (equipo_id) REFERENCES equipos(id) ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_te_equipo (equipo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6) Jugadores (viven dentro de Equipo, no globales)
CREATE TABLE IF NOT EXISTS jugadores (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  equipo_id INT UNSIGNED NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  dorsal INT UNSIGNED NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_jugadores_equipo FOREIGN KEY (equipo_id) REFERENCES equipos(id) ON UPDATE CASCADE ON DELETE CASCADE,
  UNIQUE KEY uk_jugadores_equipo_dorsal (equipo_id, dorsal),
  INDEX idx_jugadores_equipo (equipo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7) Jornadas
CREATE TABLE IF NOT EXISTS jornadas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  torneo_id INT UNSIGNED NOT NULL,
  nro INT UNSIGNED NOT NULL,
  nombre VARCHAR(120) NULL,
  fecha DATE NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_jornadas_torneo FOREIGN KEY (torneo_id) REFERENCES torneos(id) ON UPDATE CASCADE ON DELETE CASCADE,
  UNIQUE KEY uk_jornadas_torneo_nro (torneo_id, nro),
  INDEX idx_jornadas_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8) Partidos (exactamente una jornada)
CREATE TABLE IF NOT EXISTS partidos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  jornada_id INT UNSIGNED NOT NULL,
  equipoA_id INT UNSIGNED NOT NULL,
  equipoB_id INT UNSIGNED NOT NULL,
  fechaHora DATETIME NOT NULL,
  estado ENUM('programado','en_juego','finalizado','suspendido') NOT NULL DEFAULT 'programado',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_partidos_jornada FOREIGN KEY (jornada_id) REFERENCES jornadas(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_partidos_equipoA FOREIGN KEY (equipoA_id) REFERENCES equipos(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_partidos_equipoB FOREIGN KEY (equipoB_id) REFERENCES equipos(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_partidos_jornada (jornada_id),
  INDEX idx_partidos_fechaHora (fechaHora),
  INDEX idx_partidos_equipoA_fecha (equipoA_id, fechaHora),
  INDEX idx_partidos_equipoB_fecha (equipoB_id, fechaHora),
  CONSTRAINT chk_equipos_distintos CHECK (equipoA_id <> equipoB_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9) Resultados propuestos (un único PENDIENTE por partido, version optimistic locking)
CREATE TABLE IF NOT EXISTS resultados_propuestos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  partido_id INT UNSIGNED NOT NULL,
  estado ENUM('PENDIENTE','RECHAZADO','OFICIAL') NOT NULL DEFAULT 'PENDIENTE',
  motivo_rechazo TEXT NULL,
  datos JSON NOT NULL COMMENT '{goles:[{jugadorId,equipoId,cantidad}], tarjetas:[{jugadorId,equipoId,tipo}], observaciones}',
  version INT UNSIGNED NOT NULL DEFAULT 1,
  creado_por INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_resultados_partido FOREIGN KEY (partido_id) REFERENCES partidos(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_resultados_creador FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  UNIQUE KEY uk_resultados_partido_pendiente (partido_id, estado),
  INDEX idx_resultados_estado (estado),
  INDEX idx_resultados_partido (partido_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Nota: el UNIQUE parcial (solo un PENDIENTE) se refuerza en Service con SELECT ... WHERE estado='PENDIENTE'.
-- En MySQL no hay índice parcial; se usa UNIQUE(partido_id, estado) + validación applicativa.

-- 10) Usuario <-> Torneo (editores asignados N:M)
CREATE TABLE IF NOT EXISTS usuario_torneo (
  usuario_id INT UNSIGNED NOT NULL,
  torneo_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (usuario_id, torneo_id),
  CONSTRAINT fk_ut_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_ut_torneo FOREIGN KEY (torneo_id) REFERENCES torneos(id) ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_ut_torneo (torneo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11) Refresh tokens (hash, rotation, revoke)
CREATE TABLE IF NOT EXISTS refresh_tokens (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NOT NULL,
  token_hash VARCHAR(128) NOT NULL UNIQUE COMMENT 'SHA256 del refresh',
  expires_at DATETIME NOT NULL,
  revoked TINYINT(1) NOT NULL DEFAULT 0,
  rotated_to INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_rt_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_rt_usuario (usuario_id),
  INDEX idx_rt_expires (expires_at),
  INDEX idx_rt_revoked (revoked)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12) Auditoría append-only
CREATE TABLE IF NOT EXISTS audit_log (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NULL,
  accion VARCHAR(80) NOT NULL COMMENT 'ej: resultado.propuesto, resultado.aprobado, partido.creado',
  entidad VARCHAR(60) NOT NULL,
  entidad_id INT UNSIGNED NULL,
  torneo_id INT UNSIGNED NULL,
  partido_id INT UNSIGNED NULL,
  antes JSON NULL,
  despues JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_usuario (usuario_id),
  INDEX idx_audit_torneo (torneo_id),
  INDEX idx_audit_partido (partido_id),
  INDEX idx_audit_accion (accion),
  INDEX idx_audit_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13) Rate limiting (MySQL, sin Redis)
CREATE TABLE IF NOT EXISTS rate_limits (
  clave VARCHAR(180) NOT NULL COMMENT 'user:123 o ip:1.2.3.4',
  window_start DATETIME NOT NULL,
  contador INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (clave, window_start),
  INDEX idx_rl_window (window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
```

## Notas de índices y reglas

- **Solapamiento**: validar en `PartidoService` con `ABS(TIMESTAMPDIFF(MINUTE, fechaHora, ?)) < 120` por `equipoA_id`/`equipoB_id`; índices `partidos(equipoA_id, fechaHora)` y `(equipoB_id, fechaHora)` lo soportan.
- **Pendiente único**: `resultados_propuestos` usa `UNIQUE(partido_id, estado)` + check applicativo `WHERE estado='PENDIENTE'` (MySQL no soporta índice parcial).
- **Dorsal único por equipo**: `UNIQUE(equipo_id, dorsal)` permite dorsal NULL múltiples veces (MySQL permite).
- **Charset**: `utf8mb4_unicode_ci` para nombres con acentos.

## Orden de migraciones sugerido

```
001_deportes.sql
002_usuarios.sql
003_torneos.sql
004_equipos.sql
005_torneo_equipo.sql
006_jugadores.sql
007_jornadas.sql
008_partidos.sql
009_resultados_propuestos.sql
010_usuario_torneo.sql
011_refresh_tokens.sql
012_audit_log.sql
013_rate_limits.sql
```
