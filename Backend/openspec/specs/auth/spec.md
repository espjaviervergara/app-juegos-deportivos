## Requirements

### Requirement: Autenticación JWT con refresh rotation
The system SHALL autenticar usuarios vía JWT HS256 con `access` de 15 minutos en header `Authorization: Bearer <token>` y `refresh` de 7 días en cookie `httpOnly Secure` hasheado en tabla `refresh_tokens(hash, expires_at, revoked, rotated_to)`. `POST /api/v1/auth/login` emite ambos; `POST /api/v1/auth/refresh` rota (revoca anterior y emite par nuevo); `POST /api/v1/auth/logout` revoca el refresh actual. Secret en `config/secret.php` fuera de git.

#### Scenario: Login emite par access+refresh
- **WHEN** POST `/api/v1/auth/login` con credenciales válidas
- **THEN** responde 200 con `{ accessToken, expiresIn }` y `Set-Cookie: refreshToken=...; HttpOnly; Secure; SameSite=Strict` y persiste hash en `refresh_tokens`

#### Scenario: Refresh rota y revoca anterior
- **WHEN** POST `/api/v1/auth/refresh` con cookie válida no revocada
- **THEN** revoca el token previo (`revoked=1, rotated_to=nuevo_id`) y emite nuevo par; segundo uso del token viejo responde 401

#### Scenario: Logout revoca
- **WHEN** POST `/api/v1/auth/logout` autenticado
- **THEN** marca `revoked=1` y limpia cookie; refresh posterior con ese token responde 401

### Requirement: RBAC por rol y asignación a torneos
The system SHALL aplicar RBAC con roles `admin_principal` y `editor`. `admin_principal` accede a todo; `editor` solo puede escribir (proponer resultados) y leer detalle privado en torneos donde exista `usuario_torneo(usuario_id, torneo_id)`. Asignación N:M modificable en cualquier momento por admin.

#### Scenario: Editor sin asignación recibe 403
- **WHEN** editor no asignado a torneo 5 intenta POST `/api/v1/partidos/99/resultados`
- **THEN** responde 403 `{ error:{code:"FORBIDDEN", message:"Not assigned to tournament"} }`

#### Scenario: Admin asigna editor a torneo
- **WHEN** admin POST `/api/v1/torneos/5/editores` con `{ usuarioId }`
- **THEN** crea `usuario_torneo` y responde 201; editor asignado ya puede proponer resultados en ese torneo

### Requirement: Rate limiting por usuario e IP en MySQL
The system SHALL limitar escritura por `user_id` (ej. 60 req/min) y lectura pública (calendario/clasificaciones) por IP (ej. 100 req/min) usando tabla `rate_limits` con ventana deslizante y responder 429 con `Retry-After` al exceder.

#### Scenario: Escritura excede límite por usuario
- **WHEN** usuario autenticado supera 60 POST en 60s
- **THEN** responde 429 `{ error:{code:"RATE_LIMITED"} }` con header `Retry-After`

#### Scenario: Lectura pública limitada por IP
- **WHEN** IP anónima supera 100 GET `/api/v1/torneos/1/clasificaciones` en 60s
- **THEN** responde 429

### Requirement: Contratos de error y CORS en PHP
The system SHALL responder errores en formato `{ error:{code, message, details?} }` con códigos `UNAUTHORIZED, FORBIDDEN, NOT_FOUND, CONFLICT, VALIDATION_ERROR, RATE_LIMITED` y CORS vía headers PHP con allowlist en config. Soportar `X-HTTP-Method-Override` para hosting que bloquea PUT/DELETE.

#### Scenario: Error de validación con details
- **WHEN** POST con body inválido
- **THEN** 422 `{ error:{code:"VALIDATION_ERROR", details:[{field, message}] } }`
