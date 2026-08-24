## ADDED Requirements

### Requirement: CRUD manual de grupos por torneo
The system SHALL permitir a admin crear grupos por torneo vía POST /torneos/:id/grupos {nombre} con nombre único por torneo, listar GET /torneos/:id/grupos, y borrar DELETE /grupos/:id si está vacío. Solo admin.

#### Scenario: Crear grupo manual con nombre único
- **WHEN** admin POST /torneos/1/grupos {nombre:"Grupo A"}
- **THEN** 201 con grupo; nombre duplicado en mismo torneo responde 409

#### Scenario: Listar grupos de torneo
- **WHEN** GET /torneos/1/grupos
- **THEN** 200 con lista ordenada por orden/nombre

### Requirement: Asignación y reagrupación de equipos a grupos
The system SHALL permitir asignar equipos a grupos vía POST /grupos/:id/equipos {equipoId} y remover DELETE /grupos/:id/equipos/:equipoId, validando que equipo ya está en torneo (torneo_equipo) y que no está en otro grupo del mismo torneo (409). Permitir mover equipo entre grupos (reagrupar) vía PUT /grupos/reagrupar {movimientos}.

#### Scenario: Asignar equipo a grupo valida pertenencia
- **WHEN** admin POST /grupos/1/equipos {equipoId:5} donde equipo 5 está en torneo 1
- **THEN** 201; si equipo no está en torneo responde 422

#### Scenario: Equipo en dos grupos mismo torneo es 409
- **WHEN** equipo 5 ya está en Grupo A de torneo 1 e intenta POST a Grupo B mismo torneo
- **THEN** 409

#### Scenario: Reagrupar equipo entre grupos
- **WHEN** PUT /grupos/reagrupar {movimientos:[{equipoId:5, fromGrupoId:1, toGrupoId:2}]}
- **THEN** 200 y equipo queda solo en Grupo B

### Requirement: Creación automática de grupos A/B/C
The system SHALL permitir creación automática vía POST /torneos/:id/grupos/auto {numGrupos, replace:false} que genere grupos A/B/C... y reparta equipos del torneo en Round-Robin (i % numGrupos). Si replace:true borra grupos previos vacíos.

#### Scenario: Auto genera 4 grupos A-D
- **WHEN** POST /torneos/1/grupos/auto {numGrupos:4}
- **THEN** 201 con 4 grupos A-D y equipos repartidos

#### Scenario: Auto con replace borra previos
- **WHEN** POST /torneos/1/grupos/auto {numGrupos:2, replace:true}
- **THEN** borra grupos anteriores y crea 2 nuevos
