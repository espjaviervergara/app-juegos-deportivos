## Requirements

### Requirement: Listado público de torneos paginado
The system SHALL mostrar GET /torneos con paginación page/limit y filtros por deporte y categoria M/F/Mixto, sin requerir auth.

#### Scenario: Paginación y filtros
- **WHEN** usuario cambia page o filtro deporte
- **THEN** hace GET /torneos?page&limit&deporteId&categoria y renderiza cards

### Requirement: Detalle torneo con tabs
The system SHALL mostrar detalle de torneo con tabs Equipos/Jornadas/Calendario/Clasificación, con datos de GET /torneos/:id y relacionados.

#### Scenario: Navegación por tabs
- **WHEN** usuario entra a /torneos/5
- **THEN** ve tabs y al cambiar tab hace fetch correspondiente

