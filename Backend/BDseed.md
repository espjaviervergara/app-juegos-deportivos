# Seed — App Juegos Deportivos

> Inserta catálogo, 1 admin, 2 torneos, 10 equipos y 10 jugadores por equipo (100 jugadores). Idempotente si se trunca antes.
> Password del admin: `Admin123!` (hash bcrypt abajo, generado con `password_hash('Admin123!', PASSWORD_BCRYPT)`).

```sql
-- BDseed.md — Seed para app_juegos_deportivos
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

-- Limpieza (opcional, para re-seed)
TRUNCATE TABLE rate_limits;
TRUNCATE TABLE audit_log;
TRUNCATE TABLE refresh_tokens;
TRUNCATE TABLE usuario_torneo;
TRUNCATE TABLE resultados_propuestos;
TRUNCATE TABLE partidos;
TRUNCATE TABLE jornadas;
TRUNCATE TABLE jugadores;
TRUNCATE TABLE torneo_equipo;
TRUNCATE TABLE torneos;
TRUNCATE TABLE equipos;
TRUNCATE TABLE usuarios;
TRUNCATE TABLE deportes;

-- 1) Deportes (catálogo por defecto)
INSERT INTO deportes (id, nombre, activo) VALUES
(1, 'Fútbol', 1),
(2, 'Básquet', 1),
(3, 'Vóley', 1);

-- 2) Admin principal
-- password = Admin123!  (cambiar en producción)
INSERT INTO usuarios (id, nombre, email, password_hash, rol, activo) VALUES
(1, 'Administrador Principal', 'admin@juegos.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);
-- hash corresponde a 'password' en ejemplo Laravel; reemplaza por hash real de 'Admin123!' al desplegar:
-- php -r "echo password_hash('Admin123!', PASSWORD_BCRYPT);"

-- 3) Dos torneos (deporte fijo, categoría M/F/Mixto)
INSERT INTO torneos (id, deporte_id, nombre, categoria, formato, estado, fecha_inicio, fecha_fin) VALUES
(1, 1, 'Torneo Apertura Fútbol 2026', 'M', 'liga', 'activo', '2026-09-01', '2026-12-15'),
(2, 2, 'Copa Básquet Mixta 2026', 'Mixto', 'grupos+eliminatoria', 'activo', '2026-09-10', '2026-11-30');

-- 4) 10 Equipos
INSERT INTO equipos (id, nombre) VALUES
(1, 'Lobos FC'),
(2, 'Águilas Doradas'),
(3, 'Tigres del Sur'),
(4, 'Leones Central'),
(5, 'Halcones Unidos'),
(6, 'Cóndores Andinos'),
(7, 'Pumas del Norte'),
(8, 'Jaguares FC'),
(9, 'Dragones Rojos'),
(10, 'Toros Bravo');

-- 5) Inscripción N:M (cada torneo aísla historial)
-- Torneo 1 (Fútbol M): equipos 1-6
-- Torneo 2 (Básquet Mixto): equipos 5-10 (5 y 6 solapados para demostrar reutilización)
INSERT INTO torneo_equipo (torneo_id, equipo_id) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6),
(2, 5), (2, 6), (2, 7), (2, 8), (2, 9), (2, 10);

-- 6) 10 jugadores por equipo (100 total)
-- Equipo 1: Lobos FC
INSERT INTO jugadores (id, equipo_id, nombre, dorsal) VALUES
(1, 1, 'Carlos Rivas', 1), (2, 1, 'Jorge Méndez', 2), (3, 1, 'Luis Ortega', 3), (4, 1, 'Marco Díaz', 4), (5, 1, 'Andrés Silva', 5),
(6, 1, 'Diego Torres', 6), (7, 1, 'Felipe Castro', 7), (8, 1, 'Ricardo Vega', 8), (9, 1, 'Hugo Morales', 9), (10, 1, 'Iván Paredes', 10);
-- Equipo 2: Águilas Doradas
INSERT INTO jugadores (id, equipo_id, nombre, dorsal) VALUES
(11, 2, 'Matías Rojas', 1), (12, 2, 'Sebastián Núñez', 2), (13, 2, 'Gonzalo Ríos', 3), (14, 2, 'Emilio Vargas', 4), (15, 2, 'Tomás Herrera', 5),
(16, 2, 'Nicolás Fuentes', 6), (17, 2, 'Joaquín Soto', 7), (18, 2, 'Benjamín Lira', 8), (19, 2, 'Cristóbal Muñoz', 9), (20, 2, 'Vicente Campos', 10);
-- Equipo 3: Tigres del Sur
INSERT INTO jugadores (id, equipo_id, nombre, dorsal) VALUES
(21, 3, 'Franco Acosta', 1), (22, 3, 'Martín Peña', 2), (23, 3, 'Santiago Lara', 3), (24, 3, 'Pablo Medina', 4), (25, 3, 'Agustín Gil', 5),
(26, 3, 'Lautaro Bravo', 6), (27, 3, 'Ignacio Vera', 7), (28, 3, 'Facundo Reyes', 8), (29, 3, 'Bruno Farías', 9), (30, 3, 'Gabriel Soto', 10);
-- Equipo 4: Leones Central
INSERT INTO jugadores (id, equipo_id, nombre, dorsal) VALUES
(31, 4, 'Rodrigo Pérez', 1), (32, 4, 'Fabián Castillo', 2), (33, 4, 'Marcelo Díaz', 3), (34, 4, 'Esteban Quiroz', 4), (35, 4, 'Cristian Tapia', 5),
(36, 4, 'Daniel Sandoval', 6), (37, 4, 'Alejandro Ruiz', 7), (38, 4, 'Óscar Molina', 8), (39, 4, 'Patricio Zúñiga', 9), (40, 4, 'Roberto Figueroa', 10);
-- Equipo 5: Halcones Unidos
INSERT INTO jugadores (id, equipo_id, nombre, dorsal) VALUES
(41, 5, 'Francisco Vidal', 1), (42, 5, 'Jorge Contreras', 2), (43, 5, 'Mauricio Orellana', 3), (44, 5, 'Víctor Sepúlveda', 4), (45, 5, 'Claudio Henríquez', 5),
(46, 5, 'Álvaro Gutiérrez', 6), (47, 5, 'Fernando Lagos', 7), (48, 5, 'Eduardo Pinto', 8), (49, 5, 'Héctor Álvarez', 9), (50, 5, 'Jaime Cortés', 10);
-- Equipo 6: Cóndores Andinos
INSERT INTO jugadores (id, equipo_id, nombre, dorsal) VALUES
(51, 6, 'René Valenzuela', 1), (52, 6, 'Sergio Aravena', 2), (53, 6, 'Manuel Bustamante', 3), (54, 6, 'Arturo Carrasco', 4), (55, 6, 'Raúl Espinoza', 5),
(56, 6, 'Ernesto Valdivia', 6), (57, 6, 'Alberto Jara', 7), (58, 6, 'César Ulloa', 8), (59, 6, 'Julio Salazar', 9), (60, 6, 'Omar Salinas', 10);
-- Equipo 7: Pumas del Norte
INSERT INTO jugadores (id, equipo_id, nombre, dorsal) VALUES
(61, 7, 'Leonardo Ponce', 1), (62, 7, 'Rafael Escobar', 2), (63, 7, 'Guillermo Aguilera', 3), (64, 7, 'Félix Miranda', 4), (65, 7, 'Samuel Navarrete', 5),
(66, 7, 'Damián Araya', 6), (67, 7, 'Elías Saavedra', 7), (68, 7, 'Iván Garrido', 8), (69, 7, 'Maximiliano Godoy', 9), (70, 7, 'Bastián Donoso', 10);
-- Equipo 8: Jaguares FC
INSERT INTO jugadores (id, equipo_id, nombre, dorsal) VALUES
(71, 8, 'Nicolás Ramírez', 1), (72, 8, 'Felipe Andrade', 2), (73, 8, 'Diego Valdés', 3), (74, 8, 'Camilo Vergara', 4), (75, 8, 'Javier Olivares', 5),
(76, 8, 'Miguel Henríquez', 6), (77, 8, 'Lucas Fernández', 7), (78, 8, 'Matías Cortés', 8), (79, 8, 'Sebastián Peña', 9), (80, 8, 'Ángel Muñoz', 10);
-- Equipo 9: Dragones Rojos
INSERT INTO jugadores (id, equipo_id, nombre, dorsal) VALUES
(81, 9, 'Benjamín Herrera', 1), (82, 9, 'Vicente Rojas', 2), (83, 9, 'Joaquín Castillo', 3), (84, 9, 'Martín Fuentes', 4), (85, 9, 'Tomás Soto', 5),
(86, 9, 'Agustín Morales', 6), (87, 9, 'Facundo Silva', 7), (88, 9, 'Santiago Vargas', 8), (89, 9, 'Lautaro Ríos', 9), (90, 9, 'Bruno Díaz', 10);
-- Equipo 10: Toros Bravo
INSERT INTO jugadores (id, equipo_id, nombre, dorsal) VALUES
(91, 10, 'Cristóbal Lara', 1), (92, 10, 'Emilio Medina', 2), (93, 10, 'Gonzalo Bravo', 3), (94, 10, 'Ignacio Vera', 4), (95, 10, 'Pablo Gil', 5),
(96, 10, 'Franco Reyes', 6), (97, 10, 'Gabriel Farías', 7), (98, 10, 'Rodrigo Soto', 8), (99, 10, 'Matías Pavez', 9), (100, 10, 'Sebastián Ulloa', 10);

SET FOREIGN_KEY_CHECKS=1;

-- Verificación
-- SELECT COUNT(*) FROM equipos; -- 10
-- SELECT COUNT(*) FROM jugadores; -- 100
-- SELECT * FROM torneo_equipo;
-- SELECT t.nombre, d.nombre AS deporte, t.categoria FROM torneos t JOIN deportes d ON d.id=t.deporte_id;
```

## Datos de acceso

```
Admin:  admin@juegos.local / Admin123!
Torneos:
  1) Torneo Apertura Fútbol 2026 (Fútbol, M, liga) → equipos 1-6
  2) Copa Básquet Mixta 2026 (Básquet, Mixto, grupos+eliminatoria) → equipos 5-10
Equipos solapados (reutilizados): Halcones Unidos (5) y Cóndores Andinos (6) en ambos torneos
Jugadores: 10 por equipo, dorsal 1-10 único por equipo
```

## Uso

```bash
# 1) Crear BD
mysql -u root -p -e "CREATE DATABASE app_juegos_deportivos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
# 2) DDL
mysql -u root -p app_juegos_deportivos < BD.sql   # contenido de BD.md
# 3) Seed
mysql -u root -p app_juegos_deportivos < BDseed.sql # contenido de este archivo
# o copiar/pegar los bloques arriba en HeidiSQL / phpMyAdmin (Laragon)
```

> Para jornadas/partidos de prueba, usar `POST /api/v1/torneos/{id}/jornadas` y `POST /api/v1/jornadas/{id}/partidos` (valida solapamiento <120m y pertenencia al torneo).
