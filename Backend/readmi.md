# App Juegos Deportivos — Backend API

> API first para gestión de torneos multideporte. Luego consumida por web y app móvil. Hosting básico (PHP puro + MySQL, sin Redis/queue).

## 1. Resumen del proyecto

Plataforma para administrar torneos de diferentes deportes (fútbol, básquet, vóley por defecto + deportes custom). Cada torneo tiene **un deporte fijo** y **categoría M/F/Mixto**. Soporta formatos flexibles (liga, eliminación directa, grupos+eliminatoria) elegidos al crear el torneo. Fixture **100% manual y reasignable** por vuelta.

Entidades clave: `Deporte → Torneo → Jornada → Partido → ResultadoPropuesto` y `Equipo ↔ Torneo` (N:M aislado por torneo) con `Jugador` viviendo dentro de `Equipo`. Clasificaciones y calendario son **derivados y de lectura pública**. Escritura siempre autenticada con **JWT + refresh rotation**. Dos roles: `admin_principal` (gestiona todo) y `editor` (solo anota resultados en torneos asignados, N torneos asignables en cualquier momento) con **flujo de aprobación** PENDIENTE → OFICIAL/RECHAZADO+motivo.

Propuesta formal: `openspec/changes/torneos-api-v1/` (proposal, design, 8 specs, tasks).

## 2. Stack y arquitectura

- **PHP puro sin framework**, MVC, SOLID, clean code. PSR-4 via Composer (única lib externa: `firebase/php-jwt`).
- **Router 100% artesanal**: `public/index.php` front controller, `.htaccess` rewrite, clase `Router` con regex ancladas, normalización de URI, dispatcher manual y cadena de Middleware.
- **Capas**: `Controllers` finos → `Services` (reglas) → `Repositories` (PDO prepared) → `Entities` + `Validators` + `Middleware` (RateLimit, Auth, RBAC, Audit).
- **MySQL** (Laragon). Migraciones `.sql` + `php migrate.php`. Sin Redis/queue/cron.
- **Estructura**:
```
public/index.php
src/Controllers|Services|Repositories|Models|Middleware|Validators
config/app.php , config/secret.php (fuera de git)
migrations/*.sql
```

## 3. Roles y permisos

| Capacidad | admin_principal | editor |
|---|---|---|
| CRUD deportes/torneos/jornadas/partidos/equipos/jugadores | ✅ | ❌ |
| Asignar editores a torneos | ✅ | ❌ |
| Aprobar/rechazar resultado | ✅ | ❌ |
| Proponer resultado (goles/tarjetas genéricas) | ✅ | ✅ solo torneos asignados |
| Ver auditoría | ✅ | ❌ |
| Lectura pública calendario/clasificaciones | ✅ | ✅ (sin auth) |

`usuario_torneo(usuario_id, torneo_id)` N:M. Editor ve/escribe solo donde está asignado.

## 4. Modelo de datos (resumen)

```
Deporte 1—N Torneo N—M Equipo 1—N Jugador
                1—N Jornada 1—N Partido 0—1 ResultadoPropuesto {PENDIENTE|RECHAZADO|OFICIAL, motivo, version, datos JSON}
                1—1 Clasificación (vista derivada)
Usuario N—M Torneo (asignación)
AuditLog, RefreshToken, RateLimit
```

- `TorneoEquipo` aísla historial/stats por torneo aunque el equipo se reuse.
- `Jugador` no es global; equipo puede crearse vacío y añadir jugadores después.
- Estadísticas por `torneo_id + equipo_id + jugador_id` solo de partidos OFICIAL.

## 5. Rutas API (`/api/v1/`)

### Auth
```
POST /api/v1/auth/login              { email, password } → { accessToken, expiresIn } + Set-Cookie refresh
POST /api/v1/auth/refresh            (cookie) → rota y revoca anterior
POST /api/v1/auth/logout             (auth) → revoca refresh
```

### Deportes (CRUD solo admin, GET público)
```
GET    /api/v1/deportes?page&limit
POST   /api/v1/deportes              { nombre } (admin)
GET    /api/v1/deportes/{id}
PUT    /api/v1/deportes/{id}         (admin)
DELETE /api/v1/deportes/{id}         (admin)
```

### Torneos (GET público, escritura admin)
```
GET    /api/v1/torneos?page&limit&deporteId&categoria
POST   /api/v1/torneos               { nombre, deporteId, categoria:M|F|Mixto, formato:liga|eliminatoria|grupos+eliminatoria } (admin)
GET    /api/v1/torneos/{id}
PUT    /api/v1/torneos/{id}          (admin)
DELETE /api/v1/torneos/{id}          (admin)
POST   /api/v1/torneos/{id}/equipos  { equipoId } (admin, attach)
DELETE /api/v1/torneos/{id}/equipos/{equipoId} (admin, detach)
POST   /api/v1/torneos/{id}/editores { usuarioId } (admin)
DELETE /api/v1/torneos/{id}/editores/{usuarioId} (admin)
GET    /api/v1/mis-torneos           (editor, solo asignados)
```

### Equipos y jugadores (solo admin)
```
GET    /api/v1/equipos?page&limit&torneoId
POST   /api/v1/equipos               { nombre, escudo? }
GET    /api/v1/equipos/{id}
PUT    /api/v1/equipos/{id}
DELETE /api/v1/equipos/{id}
GET    /api/v1/torneos/{id}/equipos
GET    /api/v1/equipos/{id}/jugadores
POST   /api/v1/equipos/{id}/jugadores { nombre, dorsal } (dorsal único por equipo)
PUT    /api/v1/jugadores/{id}
DELETE /api/v1/jugadores/{id}
```

### Jornadas, partidos y calendario (lectura pública, escritura admin)
```
GET    /api/v1/torneos/{id}/jornadas
POST   /api/v1/torneos/{id}/jornadas { nro, fecha } (admin)
GET    /api/v1/jornadas/{id}
PUT    /api/v1/jornadas/{id}         (admin)
DELETE /api/v1/jornadas/{id}         (admin)

GET    /api/v1/torneos/{id}/calendario?page&limit          (público)
GET    /api/v1/jornadas/{id}/partidos
POST   /api/v1/jornadas/{id}/partidos { equipoAId, equipoBId, fechaHora } (admin, valida solapamiento <120m y pertenencia al torneo)
GET    /api/v1/partidos/{id}
PUT    /api/v1/partidos/{id}         { jornadaId?, equipoAId?, equipoBId?, fechaHora? } (admin, reasignable entre jornadas)
DELETE /api/v1/partidos/{id}         (admin)
```

### Resultados y aprobación
```
POST /api/v1/partidos/{id}/resultados              { goles:[{jugadorId,equipoId,cantidad}], tarjetas:[{jugadorId,equipoId,tipo:amarilla|roja}], observaciones } (admin o editor asignado, 409 si ya existe PENDIENTE)
GET  /api/v1/partidos/{id}/resultados              (auth, ve estado)
PUT  /api/v1/partidos/{id}/resultados              (con If-Match: version, 409 si desactualizado)
POST /api/v1/partidos/{id}/resultados/aprobar      (solo admin)
POST /api/v1/partidos/{id}/resultados/rechazar     { motivo } (solo admin, motivo obligatorio, 422 si falta)
# tras RECHAZADO, editor puede POST de nuevo → nuevo PENDIENTE version++
```

### Clasificaciones y estadísticas (público)
```
GET /api/v1/torneos/{id}/clasificaciones?page&limit         (público, solo OFICIAL → PJ, PG, PE, PP, GF, GC, GA, puntos; orden puntos DESC, GA DESC, GF DESC)
GET /api/v1/torneos/{id}/equipos/{equipoId}/estadisticas    (público, aislado por torneo)
GET /api/v1/torneos/{id}/jugadores/{jugadorId}/estadisticas (público)
```

### Auditoría (solo admin_principal)
```
GET /api/v1/auditoria?torneoId&partidoId&usuarioId&page&limit
```

## 6. Contratos transversales

- **Versionado**: `/api/v1/`
- **Paginación**: `?page&limit` (default 20, max 100) → `{ data, meta:{page,limit,total} }`
- **Errores**: `{ error:{code, message, details?} }` con `UNAUTHORIZED, FORBIDDEN, NOT_FOUND, CONFLICT, VALIDATION_ERROR, RATE_LIMITED`
- **Validación**: sin reglas por deporte; goles >=0, tarjetas enum, fechaHora válida, deporte activo, equipos inscritos en torneo
- **Concurrencia**: `version` + `If-Match` → 409 Conflict; `UNIQUE(partido_id) WHERE estado=PENDIENTE`
- **Solapamiento**: mismo equipo no juega 2 partidos con diferencia <120m (buffer configurable)
- **CORS**: headers en PHP con allowlist en `config/app.php`; `X-HTTP-Method-Override` para hosting que bloquea PUT/DELETE

## 7. Seguridad en hosting básico

```
HTTPS (panel) → RateLimit(MySQL: 60/min por user escritura, 100/min por IP lectura) → Auth(JWT HS256, access 15m Bearer, refresh 7d httpOnly Secure + tabla refresh_tokens con hash/rotated_to/revoked) → RBAC(rol+usuario_torneo) → Validación(PDO prepared) → Regla negocio(solapamiento, pendiente único) → Audit(append-only)
```

- Secret en `config/secret.php` fuera de git. Purga oportunista de expirados en cada request.

## 8. Instalación (hosting básico)

```bash
composer install --no-dev
# configurar config/secret.php y config/app.php (CORS, JWT secret)
php migrate.php up   # ejecuta migrations/*.sql en orden
# docroot = public/ ; si no se puede, proteger src/ y config/ con .htaccess deny
```

Smoke: `GET /api/v1/deportes`, `GET /api/v1/torneos`, `POST /api/v1/auth/login`.

## 9. Prompt para generar el frontend (web y móvil)

> Copia y pega este prompt en tu generador de frontend (v0, Lovable, Cursor, etc.):

```
Eres un generador de frontend para "App Juegos Deportivos". El backend ya existe y NO debes modificarlo.

## Contexto backend (contrato)
- Base URL: /api/v1/
- Auth: POST /auth/login → { accessToken } + cookie httpOnly refresh; usa Authorization: Bearer <accessToken> en cada request autenticado; POST /auth/refresh para rotar; POST /auth/logout.
- Roles: admin_principal (todo) y editor (solo anota resultados en torneos asignados). No intentes bypasear RBAC.
- Paginación: ?page&limit → { data, meta:{page,limit,total} }. Errores: { error:{code,message,details} }.
- Rate limit: 429 con Retry-After.
- Endpoints públicos (sin auth): GET /deportes, GET /torneos, GET /torneos/{id}/calendario, GET /torneos/{id}/clasificaciones, GET /torneos/{id}/equipos/{id}/estadisticas.
- Endpoints privados: todo lo demás requiere Bearer. Editor solo en torneos asignados (403 si no).
- Flujo resultados: POST /partidos/{id}/resultados crea PENDIENTE (409 si ya existe); PUT con If-Match: version; POST /partidos/{id}/resultados/aprobar o /rechazar {motivo} solo admin; tras RECHAZADO se puede reenviar.
- Validaciones: equipo debe estar inscrito en torneo; solapamiento <120m del mismo equipo → 409; deporte activo; dorsal único por equipo.

## Qué construir
Web admin (responsive, luego adaptable a app móvil con mismo consumo):

1. **Auth**: login, refresh silencioso al 401, logout, guard por rol.
2. **Layout**: sidebar por rol (admin: Deportes, Torneos, Equipos, Jornadas/Partidos, Auditoría; editor: Mis Torneos, Partidos asignados).
3. **Deportes**: CRUD (solo admin) + listado público.
4. **Torneos**: listado público paginado con filtros deporte/categoría; CRUD admin (deporte, M/F/Mixto, formato); asignar/remover editores; vista detalle con tabs Equipos/Jornadas/Calendario/Clasificación.
5. **Equipos y jugadores**: CRUD equipos, attach/detach a torneo, jugadores dentro de equipo (crear equipo vacío y luego añadir jugadores, dorsal único).
6. **Jornadas y partidos**: CRUD jornadas por torneo; CRUD partidos por jornada (selector equipos del torneo, datetime, validación solapamiento mostrando 409); drag o select para reasignar entre jornadas; calendario público paginado.
7. **Resultados**: editor asignado propone {goles, tarjetas} por jugador/equipo (selects de jugadores del partido); admin ve PENDIENTEs, aprueba/rechaza con motivo, reenvío versionado; mostrar estado y 409 por pendiente duplicado o version desactualizada.
8. **Clasificaciones**: tabla pública por torneo (PJ/PG/PE/PP/GF/GC/GA/puntos, orden puntos→GA→GF) + estadísticas por equipo/jugador aisladas por torneo, recalculo tras OFICIAL.
9. **Auditoría**: solo admin, tabla paginada con filtros torneo/partido/usuario.
10. **UX**: toasts para 422 (details por campo), 409, 403, 429; skeletons; empty states; confirmaciones para attach/detach/aprobar.

## Estilo y tech
- Stack a elegir: Next.js/React + Tailwind o Vue/Nuxt; fetch con interceptores para Bearer y refresh; manejo global de 401/429.
- Diseño limpio, cards para torneos, tablas para clasificaciones, badges para estado PENDIENTE/RECHAZADO/OFICIAL y categoría M/F/Mixto.
- No inventes endpoints; respeta los de arriba. Si falta un dato, muestra fallback.

## Entregables
- Páginas y componentes listos para conectar a /api/v1/ real.
- Manejo de auth/roles/paginación/errores tal cual el contrato.
- Responsive y accesible.
```

## 10. Propuesta de mejoras futuras

**Corto plazo (sin infra extra):**
- Escudo de equipo con upload local (2MB, mime validado, renombre) o URL externa; validación en `EquipoService`.
- Buffer de solapamiento configurable por torneo (no global) + validación de `fechaHora` futura.
- Cache oportunista de clasificaciones en `torneo.clasificacion_cache JSON` invalidado en aprobar.
- Filtros avanzados: torneos por estado, calendario por fecha/equipo, búsqueda por nombre.
- Export CSV de clasificaciones y calendario.

**Medio plazo (requiere hosting con cron o servicio externo ligero):**
- Notificaciones (email/webhook) al aprobar/rechazar resultado; digest diario de calendario.
- Purga programada de `refresh_tokens`/`rate_limits`/`audit_log` antiguos vía cron.
- Tabla materializada de clasificaciones con refresh incremental.
- Snapshot de plantel por `TorneoEquipo` (si se necesita plantel distinto por torneo sin romper `Jugador ∈ Equipo`).
- Paginación por cursor para auditoría y calendario con mucho volumen.

**Largo plazo (cuando haya infra):**
- Tiempo real (WebSocket/SSE) para resultados en vivo y clasificaciones.
- Generación automática de fixtures según formato (liga todos vs todos, eliminación directa con bracket).
- Estadísticas avanzadas por deporte (si se decide romper "sin restricciones") y MVP por torneo.
- Multi-tenant / multi-organización con aislamiento por organización.
- App móvil offline-first con sync de resultados pendientes.

---
*Fuente de verdad: `openspec/changes/torneos-api-v1/` — proposal, design, 8 specs y tasks. Este readme es resumen derivado.*
