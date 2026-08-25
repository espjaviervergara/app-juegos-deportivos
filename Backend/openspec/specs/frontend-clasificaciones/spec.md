## Requirements

### Requirement: Tabla clásica de clasificación
The system SHALL renderizar GET /torneos/:id/clasificaciones como tabla Bootstrap con columnas PJ/PG/PE/PP/GF/GC/GA/Pts ordenada puntos→GA→GF, paginada.

#### Scenario: Tabla ordenada correctamente
- **WHEN** GET clasificaciones devuelve 10 equipos
- **THEN** muestra tabla en orden y resalta líder

### Requirement: Estadísticas por equipo y jugador
The system SHALL mostrar modales con GET /torneos/:id/equipos/:eid/estadisticas y /jugadores/:jid/estadisticas al hacer click en equipo/jugador.

#### Scenario: Click en equipo muestra stats
- **WHEN** usuario hace click en fila de equipo
- **THEN** abre modal con PJ/PG/PE/PP/GF/GC/GA/Pts de ese equipo

#### Scenario: Aislamiento por torneo
- **WHEN** mismo equipo en dos torneos
- **THEN** stats mostradas corresponden solo al torneo actual


## Requirements


### Requirement: Tabla con medallas y resaltado
The system SHALL mostrar tabla con medallas 🥇🥈🥉 para top 3, fila líder resaltada y badges de puntos, con diseño atractivo para estudiantes.

#### Scenario: Top 3 con medallas
- **WHEN** GET /torneos/1/clasificaciones
- **THEN** filas 1-3 muestran 🥇🥈🥉 y líder con fondo destacado

