## ADDED Requirements

### Requirement: Propuesta de resultado con pendiente único y bloqueo optimista
The system SHALL permitir a `admin_principal` y a `editor` asignado proponer resultado vía `POST /api/v1/partidos/{id}/resultados` con body genérico sin restricciones por deporte: `{ goles:[{jugadorId, equipoId, cantidad}], tarjetas:[{jugadorId, equipoId, tipo:"amarilla"|"roja"}], observaciones }`. Solo un `PENDIENTE` por `partido_id` (UNIQUE parcial); `version` entero para bloqueo optimista; segundo PENDIENTE concurrente responde 409.

#### Scenario: Editor propone resultado pendiente
- **WHEN** editor asignado POST `/api/v1/partidos/10/resultados` con goles/tarjetas válidos
- **THEN** 201 `{ estado:"PENDIENTE", version:1 }`; clasificación no cambia

#### Scenario: Segundo pendiente concurrente es 409
- **WHEN** existe PENDIENTE para partido 10 y otro usuario POST al mismo partido
- **THEN** 409 `{ error:{code:"CONFLICT", message:"Pending result already exists"} }`

#### Scenario: Bloqueo optimista en actualización
- **WHEN** editor PUT `/api/v1/partidos/10/resultados` con `If-Match: 1` pero version actual es 2
- **THEN** 409 CONFLICT

### Requirement: Aprobación y rechazo con motivo por admin_principal
The system SHALL permitir solo a `admin_principal` aprobar (`POST /api/v1/partidos/{id}/resultados/aprobar`) o rechazar (`POST /api/v1/partidos/{id}/resultados/rechazar` con `{ motivo }` obligatorio) un PENDIENTE. Aprobar cambia a `OFICIAL` y recalcula clasificación/estadísticas en la misma transacción; rechazar cambia a `RECHAZADO` con motivo y habilita reenvío. Editor no puede aprobar/rechazar.

#### Scenario: Admin aprueba y recalcula
- **WHEN** admin POST `/api/v1/partidos/10/resultados/aprobar` con PENDIENTE existente
- **THEN** 200 `{ estado:"OFICIAL" }` y GET `/api/v1/torneos/5/clasificaciones` refleja puntos/goles

#### Scenario: Rechazo sin motivo es 422
- **WHEN** admin POST `/api/v1/partidos/10/resultados/rechazar` sin `motivo`
- **THEN** 422 VALIDATION_ERROR

#### Scenario: Editor intenta aprobar es 403
- **WHEN** editor POST `/api/v1/partidos/10/resultados/aprobar`
- **THEN** 403

### Requirement: Reenvío tras rechazo versionado
The system SHALL permitir nuevo POST de resultado tras `RECHAZADO`, creando nuevo `PENDIENTE` con `version++`; el `RECHAZADO` previo queda para auditoría.

#### Scenario: Reenvío tras rechazo
- **WHEN** partido 10 está RECHAZADO con motivo y editor POST nuevo resultado
- **THEN** 201 con `PENDIENTE version:2`
