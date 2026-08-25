## ADDED Requirements

### Requirement: Partidos con grupo opcional y validación
The system SHALL permitir POST /jornadas/:id/partidos con campo opcional grupoId; si se envía, ambos equipos deben pertenecer a ese grupo (grupo_equipo), caso contrario 422. Jornada puede contener partidos de varios grupos.

#### Scenario: Crear partido con grupo válido
- **WHEN** POST /jornadas/1/partidos {equipoAId:1, equipoBId:2, fechaHora:"2026-10-01T18:00", grupoId:1} donde ambos equipos están en grupo 1
- **THEN** 201 con grupo_id=1

#### Scenario: Partido con grupo inválido es 422
- **WHEN** POST con grupoId donde equipoA no está en ese grupo
- **THEN** 422

#### Scenario: Jornada con partidos de varios grupos
- **WHEN** jornada 1 tiene partido con grupo 1 y otro con grupo 2 y otro sin grupo
- **THEN** GET /torneos/1/calendario los retorna todos, agrupados por jornada luego grupo

### Requirement: Calendario agrupa por jornada y grupo
The system SHALL en GET /torneos/:id/calendario retornar partidos con equipoA_nombre, equipoB_nombre, grupo_nombre (si tiene) y agrupación por jornada_nro y grupo para UI.

#### Scenario: Calendario incluye grupo_nombre
- **WHEN** GET /torneos/1/calendario
- **THEN** cada partido incluye grupo_nombre o null, ordenado por fechaHora
