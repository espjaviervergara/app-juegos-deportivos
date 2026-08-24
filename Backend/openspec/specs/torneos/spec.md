## Requirements

### Requirement: CRUD de torneos con categoría y formato
The system SHALL permitir a `admin_principal` CRUD de torneos `torneos(id, deporte_id, nombre, categoria ENUM('M','F','Mixto'), formato ENUM('liga','eliminatoria','grupos+eliminatoria'), estado, created_at)` con validación de `deporte_id` y `categoria`. Listado paginado `GET /api/v1/torneos?page&limit&deporteId&categoria` es público.

#### Scenario: Admin crea torneo válido
- **WHEN** admin POST `/api/v1/torneos` con `{ nombre, deporteId, categoria:"Mixto", formato:"liga" }`
- **THEN** 201 con torneo creado

#### Scenario: Listado público paginado
- **WHEN** GET `/api/v1/torneos?page=1&limit=20` sin auth
- **THEN** 200 con `data` y `meta{page,limit,total}`

### Requirement: Asignación de editores a torneos
The system SHALL permitir a `admin_principal` asignar y remover editores vía `POST /api/v1/torneos/{id}/editores` y `DELETE /api/v1/torneos/{id}/editores/{usuarioId}` sobre `usuario_torneo`. Editor asignado ve torneos asignados en `GET /api/v1/mis-torneos` y puede operar solo allí.

#### Scenario: Asignar y verificar acceso
- **WHEN** admin asigna editor a torneo 5 y editor GET `/api/v1/torneos/5`
- **THEN** editor ve detalle; sin asignación a torneo 6, GET `/api/v1/torneos/6` privado responde 403 (público sigue accesible según endpoint)

#### Scenario: Remover editor revoca acceso inmediato
- **WHEN** admin DELETE `/api/v1/torneos/5/editores/9`
- **THEN** 204 y siguiente POST de editor a partido de torneo 5 responde 403
