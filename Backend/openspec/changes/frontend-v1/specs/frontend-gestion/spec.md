## ADDED Requirements

### Requirement: CRUD admin para deportes/torneos/equipos/jugadores/jornadas/partidos
The system SHALL proveer formularios CRUD para admin (deportes, torneos con validación deporte activo y M/F/Mixto, equipos, jugadores con dorsal único, jornadas, partidos con fechaHora y equipos del torneo), mostrando 409 para solapamiento <120m.

#### Scenario: Crear partido con solapamiento muestra 409
- **WHEN** admin crea partido del mismo equipo dentro de 120m
- **THEN** muestra error "Equipo con partido solapado" sin cerrar modal

#### Scenario: Attach/detach equipo a torneo
- **WHEN** admin hace attach de equipo a torneo
- **THEN** lista se actualiza y muestra toast éxito

### Requirement: Validación y paginación en gestión
The system SHALL validar campos requeridos (422 con details por campo) y paginar listados con page/limit.

#### Scenario: Validación muestra details
- **WHEN** POST sin nombre
- **THEN** marca campo con mensaje de details
