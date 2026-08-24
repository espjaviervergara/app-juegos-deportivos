## ADDED Requirements

### Requirement: Log de auditoría append-only
The system SHALL registrar en `audit_log(id, usuario_id, accion, entidad, entidad_id, torneo_id, partido_id, antes JSON, despues JSON, created_at)` toda acción de editor y admin sobre resultados (propuesta, aprobación, rechazo, reenvío), cambios de jornadas/partidos y asignaciones. Escritura desde Services dentro de la misma transacción; nunca UPDATE/DELETE sobre audit_log.

#### Scenario: Propuesta genera audit
- **WHEN** editor POST `/api/v1/partidos/10/resultados`
- **THEN** se inserta fila en audit_log con `accion="resultado.propuesto"`, `antes=null`, `despues` con payload

#### Scenario: Aprobación genera audit con antes/después
- **WHEN** admin aprueba PENDIENTE
- **THEN** audit_log registra `accion="resultado.aprobado"` con `antes:{estado:"PENDIENTE"}` y `despues:{estado:"OFICIAL"}`

### Requirement: Consulta de auditoría solo para admin_principal
The system SHALL exponer `GET /api/v1/auditoria?torneoId&partidoId&usuarioId&page&limit` solo para `admin_principal`, paginado y ordenado por `created_at DESC`, con filtros opcionales por torneo/partido/usuario.

#### Scenario: Editor no puede consultar auditoría
- **WHEN** editor GET `/api/v1/auditoria`
- **THEN** 403

#### Scenario: Admin filtra por torneo
- **WHEN** admin GET `/api/v1/auditoria?torneoId=5&page=1&limit=20`
- **THEN** 200 con solo logs de torneo 5
