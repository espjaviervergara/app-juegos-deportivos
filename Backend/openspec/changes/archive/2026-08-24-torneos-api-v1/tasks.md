## 1. Bootstrap PHP puro + Router artesanal

- [x] 1.1 Crear estructura `public/index.php` (front controller), `src/Controllers|Services|Repositories|Models|Middleware|Validators`, `config/`, `migrations/`, `.htaccess` con rewrite a `index.php` y `X-HTTP-Method-Override`
- [x] 1.2 Configurar Composer PSR-4 (`App\`), `config/secret.php` fuera de git + `.gitignore`, `config/app.php` (allowlist CORS, rate limits, buffer solapamiento, JWT expiry)
- [x] 1.3 Implementar `Router` artesanal (tabla METHOD+regex anclada, normalización URI, extracción params, 405/404) con tests de `//`, `%2F`, `..`, trailing slash
- [x] 1.4 Implementar dispatcher + cadena Middleware + `Response` JSON estándar `{ data }` / `{ error:{code,message,details} }` y helper `Request`

## 2. Base de datos y migraciones

- [x] 2.1 Crear migraciones SQL versionadas + `php migrate.php` (up/down) con PDO: `deportes` (seed fútbol/básquet/vóley), `usuarios`, `torneos`, `equipos`, `torneo_equipo`, `jugadores`, `jornadas`, `partidos`, `resultados_propuestos`, `usuario_torneo`, `refresh_tokens`, `audit_log`, `rate_limits` con FKs e índices (incl. índice solapamiento y `rate_limits(key, window_start)`)
- [x] 2.2 Seed `admin_principal` inicial (password_hash) y deportes default; script de purga oportunista para `refresh_tokens`/`rate_limits` expirados
- [x] 2.3 Repositorios base PDO (prepared statements, transacción helper) + `DeporteRepository`, `UsuarioRepository`, `TorneoRepository`

## 3. Auth JWT + RBAC + Rate limiting

- [x] 3.1 Integrar `firebase/php-jwt` HS256, `AuthService` (login, hash refresh, rotation, revoke), `POST /api/v1/auth/login|refresh|logout` con cookie httpOnly Secure
- [x] 3.2 Middleware `AuthMiddleware` (valida Bearer, expiración, 401) y `RbacMiddleware` (admin vs editor + check `usuario_torneo` por torneo/partido)
- [x] 3.3 Middleware `RateLimitMiddleware` en MySQL (60/min por user en escritura, 100/min por IP en lectura pública) con 429 + `Retry-After`
- [x] 3.4 Middleware `AuditMiddleware` helper y `AuditService` (append-only, antes/después JSON) usado desde Services

## 4. Deportes y Torneos

- [x] 4.1 `DeporteController/Service` CRUD (solo admin) + `GET /api/v1/deportes` público paginado con `page/limit`
- [x] 4.2 `TorneoController/Service` CRUD (admin) con validación `deporte_id` activo, `categoria M/F/Mixto`, `formato`, paginación pública `GET /api/v1/torneos`
- [x] 4.3 Asignación editores `POST|DELETE /api/v1/torneos/{id}/editores` (admin) + `GET /api/v1/mis-torneos` para editor

## 5. Equipos y Jugadores

- [x] 5.1 `EquipoController/Service` CRUD + `POST /api/v1/torneos/{id}/equipos` (attach) y `DELETE` (detach) vía `torneo_equipo`
- [x] 5.2 `JugadorController/Service` `POST /api/v1/equipos/{id}/jugadores` (crear sin jugadores previos permitido) + `GET /api/v1/equipos/{id}/jugadores`, validación dorsal único por equipo, solo admin
- [x] 5.3 `GET /api/v1/torneos/{id}/equipos` y aislación por torneo verificada (stats no se mezclan)

## 6. Jornadas, Partidos y Calendario

- [x] 6.1 `JornadaController/Service` CRUD por torneo (`POST /api/v1/torneos/{id}/jornadas`) + `GET /api/v1/torneos/{id}/jornadas|calendario` público paginado
- [x] 6.2 `PartidoController/Service` CRUD por jornada con validación: equipos pertenecen a torneo, `fechaHora` requerida, solapamiento <120m por equipo (transacción + 409), y reasignación entre jornadas vía `PUT /api/v1/partidos/{id}`
- [x] 6.3 Tests de solapamiento (mismo equipo 2 partidos solapados → 409; equipos distintos → 201; reasignación valida nueva fecha)

## 7. Resultados, Aprobación y Concurrencia

- [x] 7.1 `ResultadoService` con tabla `resultados_propuestos` (UNIQUE pendiente por partido, `estado`, `motivo_rechazo`, `datos JSON`, `version`): `POST /api/v1/partidos/{id}/resultados` (editor asignado o admin) con schema genérico `goles/tarjetas`, 409 si ya existe PENDIENTE
- [x] 7.2 Bloqueo optimista `If-Match: version` en PUT + 409 si desactualizado; validación `jugadorId` pertenece a equipo del partido
- [x] 7.3 `POST /api/v1/partidos/{id}/resultados/aprobar` y `/rechazar` (solo admin, rechazo exige `motivo`), transacción que cambia a OFICIAL/RECHAZADO, escribe audit_log y recalcula clasificaciones; 403 para editor; reenvío tras RECHAZADO crea nuevo PENDIENTE con `version++`

## 8. Clasificaciones y Estadísticas (lectura pública)

- [x] 8.1 Servicio de clasificación derivada solo de OFICIAL (`PJ, PG, PE, PP, GF, GC, GA, puntos`) ordenado `puntos DESC, GA DESC, GF DESC` + `GET /api/v1/torneos/{id}/clasificaciones` público paginado
- [x] 8.2 `GET /api/v1/torneos/{id}/equipos/{equipoId}/estadisticas` y `GET /api/v1/torneos/{id}/jugadores/{jugadorId}/estadisticas` aisladas por torneo
- [x] 8.3 Recálculo sincrónico en aprobación con agregación SQL indexada; invalidación de cache si se añade `torneo.clasificacion_cache`

## 9. Auditoría y Transversales

- [x] 9.1 `GET /api/v1/auditoria?torneoId&partidoId&usuarioId&page&limit` solo admin_principal, paginado, filtros y orden `created_at DESC`
- [x] 9.2 Validación estricta por endpoint (Validators), CORS headers con allowlist, `X-HTTP-Method-Override`, manejo de errores 422 con `details`, y normalización de paginación (default 20, max 100)
- [x] 9.3 Documentar API (endpoints, ejemplos, códigos de error, flujo PENDIENTE→OFICIAL) y smoke tests: login, refresh rotation, RBAC, solapamiento, pendiente único, rechazo con motivo

## 10. QA en hosting básico

- [x] 10.1 Tests unitarios Services/Repositories (PDO mock) y tests de integración de Router + Middleware + concurrencia de resultados
- [x] 10.2 Verificación en Laragon/hosting básico: docroot `public/`, `composer install --no-dev`, migraciones, refresh rotation, rate limit y CORS reales

