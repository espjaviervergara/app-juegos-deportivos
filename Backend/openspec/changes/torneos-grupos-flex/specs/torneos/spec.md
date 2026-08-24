## ADDED Requirements

### Requirement: Formato y fase flexible de torneo
The system SHALL permitir que admin cambie formato de torneo vía PUT /torneos/:id {formato} entre liga/eliminatoria/grupos+eliminatoria en cualquier momento, sin transición automática.

#### Scenario: Cambiar formato de liga a grupos+eliminatoria
- **WHEN** PUT /torneos/1 {formato:"grupos+eliminatoria"}
- **THEN** 200 y torneo actualiza formato

### Requirement: Eliminatoria a elección del admin
The system SHALL no crear fase eliminatoria automáticamente; admin crea jornadas/partidos de eliminatoria manualmente cuando decide, usando mismo flujo de partidos (sin grupo o con grupo).

#### Scenario: Admin crea eliminatoria manualmente
- **WHEN** admin crea jornada "Semifinal" y partidos entre ganadores de grupos
- **THEN** partidos se crean sin grupo_id y se muestran en calendario
