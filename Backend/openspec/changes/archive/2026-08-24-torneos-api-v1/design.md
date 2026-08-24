## Context

Greenfield: `Backend/` y `Frontend/` vacíos, solo `openspec/` inicializado. Se construye API first para torneos multideporte que luego consumen web y app móvil. Dominio: torneos con deporte fijo y categoría M/F/Mixto, equipos reutilizables entre torneos, jornadas/partidos con fecha mutable, calendario y clasificaciones, y dos roles con flujo de aprobación.

Restricciones duras: PHP puro sin framework, router 100% artesanal, principios SOLID y clean code, hosting básico (solo PHP + MySQL, sin Redis, queue, cron, ni config de vhost). MySQL vía Laragon. Lectura pública para calendario/clasificaciones; escritura siempre autenticada. El equipo se crea sin jugadores y se añaden después; jugador vive dentro de equipo (no global). Fixture manual. Deportes con catálogo default (fútbol, básquet, vóley) pero ABM libre. Concurrencia en resultados y solapamiento de partidos deben resolverse a nivel aplicación/BD.

## Goals / Non-Goals

**Goals:**
- API REST `/api/v1/` agnóstica para web y móvil con contratos estables (paginación `page/limit`, errores `{ error:{code,message,details} }`).
- Seguridad por capas sin infra extra: JWT con refresh rotation/revocación, RBAC por rol y por torneo asignado, rate limiting en MySQL, validación estricta, auditoría append-only, bloqueo optimista.
- Modelo de datos que aísle cada torneo (TorneoEquipo) y soporte flujo PENDIENTE→APROBADO/RECHAZADO+motivo con recálculo de standings solo tras OFICIAL.
- Arquitectura MVC pura testeable y mantenible en hosting básico.

**Non-Goals:**
- Tiempo real / websockets / notificaciones push.
- Generación automática de fixtures ni reglas por deporte.
- Uploads a S3 / CDN, ni panel admin web en esta fase (solo API).
- Soporte multi-idioma, ni OAuth externo.

## Decisions

**D1 — Router artesanal + Front Controller + PSR-4 (sin framework)**
*Decisión:* `public/index.php` único, `.htaccess` reescribe a él, clase `Router` artesanal con tabla `METHOD + regex` y extracción de params, dispatcher manual y cadena de Middleware. Autoload via Composer PSR-4 aunque el router sea propio.
*Alternativas:* FastRoute/Slim (descartado por requisito 100% artesanal); sin composer (descartado: rompe SOLID y autoload).
*Razón:* Cumple restricción y mantiene separación Controller→Service→Repository testeable.

**D2 — Capas MVC puras con interfaces**
*Decisión:* `Controllers` finos (HTTP→Service), `Services` con reglas (solapamiento, aprobación, cálculo tabla), `Repositories` con PDO prepared, `Entities` sin SQL, `Validators` por endpoint, `Middleware` (RateLimit, Auth, RBAC, Audit).
*Alternativa:* ActiveRecord / SQL en controllers (descartado: acopla y dificulta tests).
*Razón:* SOLID en PHP puro; permite mockear Repositories en tests sin framework.

**D3 — Auth JWT HS256 + refresh en httpOnly cookie + tabla `refresh_tokens`**
*Decisión:* `firebase/php-jwt` única dependencia externa; `access` 15m en `Authorization: Bearer`, `refresh` 7d hasheado en BD (`hash, expires_at, revoked, rotated_to`). `POST /auth/refresh` rota y revoca anterior; `POST /auth/logout` revoca. Secret en `config/secret.php` fuera de git, env por `config.php`.
*Alternativas:* JWT sin refresh (descartado: ventana de robo larga); sesiones PHP (descartado: no sirve para móvil).
*Razón:* Seguro en hosting básico sin Redis; rotation mitiga robo.

**D4 — RBAC por rol + asignación N:M a torneos**
*Decisión:* `usuarios(rol: admin|editor)` + `usuario_torneo(usuario_id, torneo_id)`. Middleware RBAC verifica rol y, para editor, `exists(usuario_torneo where torneo_id = recurso)`. Editor ve/escribe solo torneos asignados; admin ve todo.
*Alternativa:* ACL por endpoint (sobrediseño para 2 roles).
*Razón:* Simple y cubre "editor con N torneos asignables en cualquier momento".

**D5 — Rate limiting en MySQL**
*Decisión:* Tabla `rate_limits(key, window_start, count)` con `key = user_id` para escritura y `key = ip` para lectura pública. Middleware incrementa en transacción y responde `429` con `Retry-After`.
*Alternativas:* Redis / APCu (no disponible en hosting básico).
*Razón:* Funciona sin infra; compromiso: contención en alta concurrencia, aceptable para este dominio.

**D6 — Validación de solapamiento por equipo**
*Decisión:* En `PartidoService::create/update`, `SELECT COUNT(*) FROM partidos JOIN jornadas ON ... WHERE torneo_id=? AND partido_id<>? AND equipo_id IN (A,B) AND ABS(TIMESTAMPDIFF(MINUTE, fechaHora, ?)) < buffer` (buffer 120m configurable). Transacción + índice `partidos(equipoA_id, equipoB_id, fechaHora)`.
*Alternativa:* Validar solo en app (descartado: race condition).
*Razón:* Garantiza invariante aunque admin reasigne entre jornadas/vueltas.

**D7 — Resultado con estado y bloqueo optimista**
*Decisión:* Tabla `resultados_propuestos(partido_id UNIQUE WHERE estado='PENDIENTE', estado ENUM('PENDIENTE','RECHAZADO','OFICIAL'), motivo_rechazo, datos JSON {goles:[{jugador_id,goles}], tarjetas:[{jugador_id,tipo}]}, version INT)`. `UNIQUE(partido_id)` parcial vía índice + check en Service; `version` para `If-Match` / `409 Conflict`. Solo `admin_principal` aprueba/rechaza (rechazo exige motivo). Reenvío crea nuevo PENDIENTE con `version++`. Clasificación recalculada sincrónicamente en la misma transacción de aprobación (agregación SQL sobre partidos OFICIAL).
*Alternativas:* Fila por intento (histórico) — descartado por "solo quedará un registro"; cola async — no hay infra.
*Razón:* Cumple "un solo pendiente", auditabilidad y consistencia de standings.

**D8 — Auditoría y clasificación**
*Decisión:* `audit_log` append-only escrito desde Services (no Repositories) con `antes/después` JSON. Clasificación como vista derivada (`SELECT equipo_id, SUM(P-E-D...), GF, GC, GA... GROUP BY`) sin tabla materializada; paginada y pública.
*Alternativa:* Tabla materializada con cron — no hay cron.
*Razón:* Correcta y simple en hosting básico; coste aceptable con índices.

**D9 — Contratos transversales**
*Decisión:* Versionado en URL, paginación `?page&limit` (default 20, max 100), errores estándar, validación JSON schema manual, CORS headers en PHP con allowlist en config, `.htaccess` para `X-HTTP-Method-Override`.
*Razón:* Predecible para web y móvil sin dependencias.

## Risks / Trade-offs

- **Router artesanal mal normalizado → bypass RBAC / traversal** → Mitigación: normalizar URI (`trim`, `rawurldecode`, colapsar `//`, rechazar `..`), regex ancladas `^/api/v1/...$`, tests de router con casos `//`, `%2F`, `..`.
- **Rate limit en MySQL contencioso bajo picos** → Mitigación: ventana 60s, `INSERT ... ON DUPLICATE KEY UPDATE` atómico, índice `rate_limits(key, window_start)`, degradar a `429` temprano.
- **JWT secret en hosting básico expuesto si config versionado** → Mitigación: `config/secret.php` fuera de git, `.gitignore`, rotación manual documentada, `hash` de refresh en BD nunca en claro.
- **Solapamiento con buffer fijo puede ser restrictivo** → Trade-off: buffer configurable por torneo (default 120m) evita falsos positivos en deportes cortos.
- **Jugador dentro de Equipo + Equipo reusable → plantel compartido entre torneos** → Mitigación: documentado como decisión; si en el futuro necesita plantel por torneo, migrar a `torneo_equipo_jugador` snapshot (migración prevista).
- **Clasificación sincrónica en aprobación puede alargar request** → Mitigación: agregación indexada, sin N+1; si crece, cache en `torneo.clasificacion_cache JSON` invalidado en aprobación.
- **Sin cron/queue no hay limpieza automática de refresh_tokens/rate_limits** → Mitigación: purga oportunista en cada request (`DELETE WHERE expires_at < NOW() LIMIT 100`) + endpoint admin de purga.
- **Hosting sin PUT/DELETE** → Mitigación: `X-HTTP-Method-Override` y documentar fallback a POST.

## Migration Plan

1. Migraciones SQL versionadas `migrations/001_*.sql` + `php migrate.php` (sin framework). Orden: `deportes` seed → `usuarios` → `torneos` → `equipos` → `torneo_equipo` → `jugadores` → `jornadas` → `partidos` → `resultados_propuestos` → `usuario_torneo` → `refresh_tokens` → `audit_log` → `rate_limits`. Índices y FKs incluidos.
2. Seed deportes default y usuario `admin_principal` (password hasheado `password_hash`).
3. Deploy: subir `public/` como docroot, `src/` y `config/` fuera de docroot cuando el panel lo permita; si no, proteger con `.htaccess deny`. `composer install --no-dev`.
4. Smoke: `GET /api/v1/deportes`, `GET /api/v1/torneos` (público), `POST /api/v1/auth/login`.
5. Rollback: migraciones `DOWN` por archivo; JWT secret rotado invalida tokens previos (revocación masiva vía `TRUNCATE refresh_tokens`).

## Open Questions

- ¿Buffer de solapamiento configurable por torneo o global 120m fijo?
- ¿Límite de rate limit diferenciado por rol (admin más alto) o único?
- ¿Escudo de equipo: upload local con límite (ej. 2MB) o solo URL externa?
