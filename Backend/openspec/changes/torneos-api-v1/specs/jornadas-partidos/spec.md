## ADDED Requirements

### Requirement: CRUD de jornadas por torneo
The system SHALL gestionar `jornadas(id, torneo_id, nro, fecha)` con CRUD para `admin_principal`. Jornada tiene fecha fija pero mutable; `GET /api/v1/torneos/{id}/jornadas` y `GET /api/v1/torneos/{id}/calendario` son públicos paginados y ordenados por fecha.

#### Scenario: Admin crea jornada y la reordena
- **WHEN** admin POST `/api/v1/torneos/5/jornadas` con `{ nro:1, fecha:"2026-09-01" }` y luego PUT `/api/v1/jornadas/3` con nueva fecha
- **THEN** 201 y 200; calendario refleja nuevo orden

#### Scenario: Calendario público sin auth
- **WHEN** GET `/api/v1/torneos/5/calendario?page=1&limit=20` sin auth
- **THEN** 200 con partidos ordenados por fechaHora

### Requirement: CRUD de partidos con validación de solapamiento
The system SHALL gestionar `partidos(id, jornada_id, equipoA_id, equipoB_id, fechaHora)` donde partido pertenece exactamente a una jornada. Solo `admin_principal` crea/edita y puede reasignar partido entre jornadas/vueltas. En create/update el sistema SHALL validar solapamiento: mismo `equipoA` o `equipoB` no puede tener dos partidos con `ABS(TIMESTAMPDIFF(MINUTE, fechaHora, nueva)) < 120` (buffer configurable). Equipos deben pertenecer al torneo vía `torneo_equipo`.

#### Scenario: Crear partido válido
- **WHEN** admin POST `/api/v1/jornadas/1/partidos` con `{ equipoAId:1, equipoBId:2, fechaHora:"2026-09-10T18:00:00" }`
- **THEN** 201

#### Scenario: Solapamiento rechazado
- **WHEN** admin intenta crear segundo partido del mismo equipo a las 19:00 del mismo día (dentro de 120m)
- **THEN** 409 `{ error:{code:"CONFLICT", message:"Team has overlapping match"} }`

#### Scenario: Reasignar partido entre jornadas
- **WHEN** admin PUT `/api/v1/partidos/7` con `{ jornadaId:2 }`
- **THEN** 200 y partido aparece en jornada 2 tras validar solapamiento en nueva fecha

### Requirement: Validación de equipos del torneo
The system SHALL rechazar partido si equipoA o equipoB no están inscritos en el torneo de la jornada.

#### Scenario: Equipo no inscrito rechaza
- **WHEN** POST partido con equipo no adjunto a torneo 5
- **THEN** 422 VALIDATION_ERROR
