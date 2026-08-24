## ADDED Requirements

### Requirement: Calendario como cards por jornada
The system SHALL renderizar GET /torneos/:id/calendario como cards por jornada (nro + fecha) que listan partidos (equipoA vs equipoB, fechaHora, badge PENDIENTE/RECHAZADO/OFICIAL), paginado y ordenado por fechaHora.

#### Scenario: Cards ordenadas por fecha
- **WHEN** GET /torneos/5/calendario devuelve 20 partidos
- **THEN** agrupa por jornada y muestra cards en orden cronológico

#### Scenario: Calendario público sin auth
- **WHEN** usuario anónimo entra a /torneos/5/calendario
- **THEN** ve calendario sin necesidad de login

### Requirement: Paginación de calendario
The system SHALL paginar calendario con page/limit contra backend.

#### Scenario: Cambio de página
- **WHEN** usuario hace click en Siguiente
- **THEN** fetch page+1 y renderiza nuevas cards
