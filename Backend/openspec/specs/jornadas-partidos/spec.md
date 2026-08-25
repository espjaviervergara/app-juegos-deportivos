## Requirements

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

## Requirements


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


## Requirements


### Requirement: Partidos pueden quedar sin jornada (borrador)
The system SHALL permitir partidos con jornada_id null (sin asignar) y reasignarlos vía PUT /partidos/:id {jornadaId, fechaHora, grupoId} validando pertenencia y solapamiento.

#### Scenario: Partido sin jornada se lista en sin-asignar
- **WHEN** GET /torneos/1/partidos/sin-asignar
- **THEN** 200 con partidos jornada_id null

#### Scenario: Reasignar sin asignar a jornada
- **WHEN** PUT /partidos/5 {jornadaId:2}
- **THEN** 200 y partido aparece en calendario de jornada 2

### Requirement: Validación de grupo y solapamiento se mantiene con jornada nullable
The system SHALL seguir validando equipo en torneo/grupo y solapamiento <120m aunque jornada_id sea null.

#### Scenario: Partido sin jornada con grupo inválido es 422
- **WHEN** POST sin jornada pero con grupoId donde equipo no está
- **THEN** 422

