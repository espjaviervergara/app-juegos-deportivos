## Requirements

### Requirement: Login y refresh silencioso con Bearer
The system SHALL proveer login vía POST /api/v1/auth/login, guardar accessToken en memoria y refresh en cookie httpOnly, y reintentar automáticamente con POST /api/v1/auth/refresh al recibir 401, encolando peticiones concurrentes.

#### Scenario: Login exitoso guarda sesión
- **WHEN** usuario envía email y password válidos en /login
- **THEN** guarda accessToken y user {id, rol} en AuthContext y redirige al dashboard

#### Scenario: Refresh silencioso al 401
- **WHEN** una petición con Bearer expirado recibe 401
- **THEN** hace refresh con cookie y reintenta la petición original sin mostrar login

#### Scenario: Logout limpia sesión
- **WHEN** usuario hace logout
- **THEN** llama POST /auth/logout, limpia tokens y redirige a /login

### Requirement: Guards por rol y asignación
The system SHALL proteger rutas por rol (admin vs editor) y por torneo asignado, mostrando 403 amigable si editor no está asignado.

#### Scenario: Editor sin asignación ve 403
- **WHEN** editor navega a torneo no asignado
- **THEN** muestra página 403 con mensaje "No asignado a este torneo"

#### Scenario: Ruta admin bloqueada para editor
- **WHEN** editor intenta acceder a /admin/auditoria
- **THEN** redirige a 403 o oculta el link en sidebar

