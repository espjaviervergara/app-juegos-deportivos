## Requirements

### Requirement: Dashboard único con permisos
The system SHALL mostrar un único dashboard con sidebar filtrada por rol (admin ve Deportes/Torneos/Equipos/Auditoría, editor ve Mis Torneos/Partidos) y layout responsive Tailwind+Bootstrap.

#### Scenario: Sidebar admin vs editor
- **WHEN** admin logueado ve sidebar
- **THEN** ve todos los items; editor ve solo asignados

#### Scenario: Manejo global de errores
- **WHEN** API responde 429 o 409
- **THEN** muestra toast con Retry-After o mensaje "Solapamiento"

### Requirement: Manejo de estados vacíos y loading
The system SHALL mostrar skeletons, empty states y toasts para 422 (details por campo).

#### Scenario: Lista vacía muestra empty state
- **WHEN** GET /torneos devuelve data vacía
- **THEN** muestra "No hay torneos" con CTA crear (solo admin)


## Requirements


### Requirement: Header con identidad del colegio
The system SHALL mostrar header con nombre corto “IE Ángela María Torres Suárez” y subtexto “Becerril Cesar”, con colores institucionales y botón “Acceso Administrador / Ayudante” destacado.

#### Scenario: Header muestra identidad
- **WHEN** usuario entra a cualquier página
- **THEN** ve header con nombre del colegio y botón acceso

