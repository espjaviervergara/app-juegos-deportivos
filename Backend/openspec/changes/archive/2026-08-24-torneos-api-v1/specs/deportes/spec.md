## ADDED Requirements

### Requirement: Catálogo de deportes con seed y ABM
The system SHALL mantener catálogo `deportes(id, nombre UNIQUE, activo)` con seed por defecto (fútbol, básquet, vóley) y CRUD solo para `admin_principal`. Un torneo referencia exactamente un `deporte_id`.

#### Scenario: Listado público de deportes activos
- **WHEN** GET `/api/v1/deportes` sin auth
- **THEN** 200 `{ data:[{id, nombre}], meta:{page,limit,total} }` con solo `activo=1`

#### Scenario: Admin crea deporte custom
- **WHEN** admin POST `/api/v1/deportes` con `{ nombre:"Handball" }`
- **THEN** 201 con deporte creado; nombre duplicado responde 409

#### Scenario: Editor no puede crear deporte
- **WHEN** editor POST `/api/v1/deportes`
- **THEN** 403

### Requirement: Deporte fijo por torneo
The system SHALL exigir `deporte_id` al crear torneo y validar que exista y esté activo; no se permite torneo sin deporte ni con múltiples deportes.

#### Scenario: Crear torneo sin deporte falla
- **WHEN** POST `/api/v1/torneos` sin `deporteId`
- **THEN** 422 VALIDATION_ERROR

#### Scenario: Crear torneo con deporte inactivo falla
- **WHEN** POST con `deporteId` de deporte `activo=0`
- **THEN** 422
