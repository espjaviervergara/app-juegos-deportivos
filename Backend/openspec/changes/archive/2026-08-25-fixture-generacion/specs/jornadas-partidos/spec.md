## ADDED Requirements

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
