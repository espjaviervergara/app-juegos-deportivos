## Why

No existe backend para gestionar torneos multideporte. Se necesita una API primero (que luego consumirán web y app móvil) que centralice torneos, equipos/jugadores, jornadas, partidos, calendario y clasificaciones con un modelo de dos roles (admin_principal y editor) y flujo de aprobación de resultados. El hosting es básico (PHP + MySQL sin Redis/queue/config server), por lo que el diseño debe ser autosuficiente, seguro y sin dependencias externas.

## What Changes

- API REST versionada en `/api/v1/` con router 100% artesanal, MVC puro en PHP (PSR-4, SOLID, clean code) y PDO prepared statements.
- Autenticación JWT: `access` corto (15m) en header `Authorization: Bearer` + `refresh` en cookie `httpOnly Secure` con tabla `refresh_tokens`, rotación y revocación. Login/refresh/logout.
- RBAC por rol y por asignación: `admin_principal` gestiona todo; `editor` solo anota resultados en torneos asignados (N torneos, asignación en cualquier momento) y ve estadísticas solo si se le permite. Lectura pública (sin JWT) para calendario y clasificaciones.
- Catálogo de deportes: seed por defecto (fútbol, básquet, vóley) + ABM libre por admin. Un torneo tiene un deporte fijo; categoría del torneo `M/F/Mixto`.
- Torneos con formato flexible elegido al crear (liga, eliminación directa, grupos+eliminatoria) y múltiples torneos simultáneos. Fixture 100% manual y reasignable por vuelta.
- Equipos reutilizables N:M entre torneos vía `TorneoEquipo` (cada torneo aísla historial/stats). Jugador vive dentro de Equipo (`Equipo 1:N Jugador`); equipo puede crearse sin jugadores y añadirlos después. Estadísticas por jugador y equipo se derivan de resultados oficiales.
- Jornadas y partidos: jornada con fecha/hora fija pero mutable; partido pertenece exactamente a una jornada; validación de solapamiento (mismo equipo no puede tener dos partidos solapados) y reasignación entre jornadas/vueltas por admin.
- Resultados con flujo de aprobación: un único `PENDIENTE` por partido, `RECHAZADO` con motivo obligatorio y reenvío versionado, `OFICIAL` (solo admin_principal aprueba). Clasificación (P-E-D, GF/GC/GA, criterios de desempate) se recalcula solo al pasar a OFICIAL.
- Auditoría append-only de toda acción de editor (quién/qué/cuándo/antes-después) y rate limiting por usuario (escritura) y por IP (lectura pública) implementado en MySQL (sin Redis).
- Contratos transversales: paginación `page/limit`, errores JSON estándar `{ error: { code, message, details } }`, validación estricta sin reglas por deporte, bloqueo optimista por `version` para concurrencia.

## Capabilities

### New Capabilities
- `auth`: JWT access+refresh con rotación/revocación, login/refresh/logout, RBAC por rol y asignación a torneos, rate limiting por usuario/IP en MySQL.
- `deportes`: catálogo seed + CRUD de deportes, validación de deporte fijo por torneo.
- `torneos`: CRUD de torneos (deporte, categoría M/F/Mixto, formato), listado público paginado, asignación de editores a torneos.
- `equipos-jugadores`: CRUD de equipos, attach/detach a torneos vía TorneoEquipo, CRUD de jugadores dentro de equipo, reutilización N:M.
- `jornadas-partidos`: CRUD de jornadas por torneo, CRUD de partidos por jornada, validación de solapamiento por equipo, reasignación entre jornadas.
- `resultados-aprobacion`: propuesta de resultado (goles/tarjetas genéricas por jugador/equipo), estado PENDIENTE único por partido, rechazo con motivo, aprobación, versionado y 409 Conflict.
- `clasificaciones`: tabla/standings derivada por torneo (P-E-D, GF/GC/GA, desempate), recalculo solo tras OFICIAL, lectura pública paginada.
- `auditoria`: log append-only de acciones de editor/admin, consulta filtrada por torneo/partido/usuario solo para admin_principal.

### Modified Capabilities
- Ninguna (proyecto greenfield, `openspec/specs/` vacío).

## Impact

- Nuevo backend PHP puro bajo `Backend/public/index.php` (front controller) + `src/Controllers|Services|Repositories|Models|Middleware|Validators` + `config/` + migraciones `.sql`.
- Base de datos MySQL: tablas `deportes, torneos, equipos, torneo_equipo, jugadores, jornadas, partidos, resultados_propuestos, usuarios, usuario_torneo, refresh_tokens, audit_log, rate_limits`.
- APIs afectadas: todas nuevas bajo `/api/v1/`; clientes futuros web y móvil consumen los mismos endpoints (lectura pública sin auth, escritura con JWT).
- Dependencias: solo `firebase/php-jwt` (permitida) para JWT; resto artesanal; composer para PSR-4. Sin Redis/queue/cron.
- Riesgos mitigados en diseño: solapamiento, race condition en resultados, JWT sin revocación, router artesanal y hosting básico (ver `design.md`).
