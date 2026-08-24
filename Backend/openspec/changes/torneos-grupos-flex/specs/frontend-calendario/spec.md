## ADDED Requirements

### Requirement: Calendario muestra grupo del partido
The system SHALL en cards por jornada mostrar badge de grupo (si partido tiene grupo_nombre) y agrupar visualmente por jornada y luego por grupo.

#### Scenario: Card jornada con dos grupos
- **WHEN** jornada 1 tiene partido Grupo A y partido Grupo B
- **THEN** card muestra sección "Grupo A" y "Grupo B" con sus partidos

### Requirement: Jornada puede tener partidos de varios grupos
The system SHALL permitir que una jornada liste partidos de distintos grupos sin error.

#### Scenario: Jornada mixta se renderiza sin error
- **WHEN** GET /torneos/1/calendario devuelve jornada con 3 grupos distintos
- **THEN** UI los agrupa correctamente sin colapsar
