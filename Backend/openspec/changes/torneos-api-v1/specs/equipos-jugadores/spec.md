## ADDED Requirements

### Requirement: CRUD de equipos reutilizables entre torneos
The system SHALL gestionar `equipos(id, nombre, escudo_path)` con CRUD para `admin_principal` y `torneo_equipo(torneo_id, equipo_id)` como inscripción aislada por torneo (cada torneo aísla historial/stats). Equipo puede attach/detach a torneo sin duplicar equipo.

#### Scenario: Crear equipo y adjuntar a torneo
- **WHEN** admin POST `/api/v1/equipos` con `{ nombre:"Lobos" }` y luego POST `/api/v1/torneos/5/equipos` con `{ equipoId }`
- **THEN** 201 en ambos; GET `/api/v1/torneos/5/equipos` lista "Lobos"

#### Scenario: Mismo equipo en dos torneos aislado
- **WHEN** "Lobos" está en torneo 5 y 6
- **THEN** stats y partidos de torneo 5 no afectan a torneo 6

#### Scenario: Equipo sin torneo no aparece en calendario
- **WHEN** equipo creado pero no adjunto a torneo
- **THEN** no aparece en `GET /api/v1/torneos/5/equipos`

### Requirement: Jugadores dentro de equipo
The system SHALL gestionar `jugadores(id, equipo_id, nombre, dorsal)` donde jugador vive dentro de equipo (no global). Equipo puede crearse sin jugadores y añadirlos después. Solo `admin_principal` gestiona jugadores.

#### Scenario: Crear equipo vacío y añadir jugadores después
- **WHEN** admin POST `/api/v1/equipos/10/jugadores` con `{ nombre:"Juan Pérez", dorsal:9 }`
- **THEN** 201 y GET `/api/v1/equipos/10/jugadores` lista al jugador

#### Scenario: Validación de dorsal duplicado por equipo
- **WHEN** admin crea segundo jugador con mismo dorsal en mismo equipo
- **THEN** 409 CONFLICT

### Requirement: Estadísticas por jugador y equipo por torneo
The system SHALL derivar estadísticas por `torneo_id + equipo_id + jugador_id` solo de partidos con resultado OFICIAL; no se mezclan entre torneos aunque el equipo sea el mismo.

#### Scenario: Gol en torneo 5 no cuenta en torneo 6
- **WHEN** resultado OFICIAL en torneo 5 registra gol de jugador 1
- **THEN** GET `/api/v1/torneos/5/equipos/10/estadisticas` incrementa; GET `/api/v1/torneos/6/equipos/10/estadisticas` no cambia
