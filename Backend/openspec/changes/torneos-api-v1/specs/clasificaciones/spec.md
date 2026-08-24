## ADDED Requirements

### Requirement: Tabla de clasificaciones derivada y pública
The system SHALL exponer `GET /api/v1/torneos/{id}/clasificaciones?page&limit` público, derivado solo de partidos con resultado `OFICIAL`. Campos por equipo: `PJ, PG, PE, PP, GF, GC, GA, puntos` (3 por victoria, 1 por empate) y orden por `puntos DESC, GA DESC, GF DESC` con criterios de desempate documentados. Paginado y ordenado.

#### Scenario: Clasificación solo cuenta OFICIAL
- **WHEN** torneo tiene 2 partidos: uno OFICIAL (victoria A) y uno PENDIENTE (victoria B)
- **THEN** GET clasificaciones muestra solo puntos del OFICIAL

#### Scenario: Lectura pública sin auth
- **WHEN** GET `/api/v1/torneos/5/clasificaciones` sin auth
- **THEN** 200 con `data` ordenada y `meta`

### Requirement: Recálculo sincrónico tras aprobación
The system SHALL recalcular clasificaciones sincrónicamente dentro de la transacción de aprobación a OFICIAL (agregación SQL), sin tabla materializada ni cron, con purga oportunista de cache si existe.

#### Scenario: Aprobación dispara recálculo
- **WHEN** admin aprueba resultado de partido 10
- **THEN** siguiente GET clasificaciones refleja inmediatamente nuevos PG/GF/puntos

### Requirement: Estadísticas por equipo y jugador
The system SHALL exponer `GET /api/v1/torneos/{id}/equipos/{equipoId}/estadisticas` y `GET /api/v1/torneos/{id}/jugadores/{jugadorId}/estadisticas` derivados de OFICIAL, aislados por torneo.

#### Scenario: Estadística aislada por torneo
- **WHEN** mismo equipo en dos torneos, gol OFICIAL solo en uno
- **THEN** estadística del otro torneo no incrementa
