## Requirements

### Requirement: Propuesta de resultados por editor
The system SHALL permitir a editor asignado proponer resultados vía POST /partidos/:id/resultados con goles/tarjetas por jugador/equipo, mostrando 409 si ya existe PENDIENTE y 422 si jugador no pertenece al equipo.

#### Scenario: Propuesta exitosa crea PENDIENTE
- **WHEN** editor envía goles/tarjetas válidos
- **THEN** muestra badge PENDIENTE y toast éxito

#### Scenario: Segundo PENDIENTE muestra 409
- **WHEN** existe PENDIENTE y editor reintenta
- **THEN** muestra "Ya existe resultado pendiente"

### Requirement: Aprobación y rechazo por admin
The system SHALL permitir solo a admin aprobar (POST /aprobar) o rechazar con motivo (POST /rechazar) un PENDIENTE, mostrando 403 para editor, y permitir reenvío tras RECHAZADO con version++.

#### Scenario: Admin aprueba y recalcula
- **WHEN** admin aprueba PENDIENTE
- **THEN** badge cambia a OFICIAL y clasificación se refresca

#### Scenario: Rechazo sin motivo muestra 422
- **WHEN** admin rechaza sin motivo
- **THEN** marca campo motivo como requerido

#### Scenario: Editor intenta aprobar ve 403
- **WHEN** editor hace click en Aprobar
- **THEN** botón oculto o 403 toast

