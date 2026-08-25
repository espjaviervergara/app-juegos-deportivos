## ADDED Requirements

### Requirement: UI para gestión de grupos manual y automático
The system SHALL mostrar en TorneoDetalle tab Grupos con: lista de grupos (A/B/C), crear grupo manual (input nombre), crear automático (input numGrupos + botón Generar), y para cada grupo multiselect de equipos del torneo para añadir y botón Mover para reagrupar.

#### Scenario: Crear grupo manual desde UI
- **WHEN** admin escribe "Grupo A" y hace click Crear
- **THEN** POST /torneos/:id/grupos y lista se actualiza

#### Scenario: Auto genera grupos A-D desde UI
- **WHEN** admin pone 4 y hace click Generar
- **THEN** POST /torneos/:id/grupos/auto y muestra 4 cards con equipos repartidos

### Requirement: Partido form filtra equipos por grupo
The system SHALL en form de partido mostrar select Grupo (opcional, lista de grupos del torneo) y filtrar selects EquipoA/B a equipos de ese grupo si se seleccionó, o a todos los del torneo si no.

#### Scenario: Seleccionar grupo filtra equipos
- **WHEN** usuario elige Grupo A en form partido
- **THEN** selects EquipoA/B solo muestran equipos de Grupo A

### Requirement: TorneoDetalle muestra creación completa
The system SHALL en TorneoDetalle permitir: ver/crear equipos, ver/crear grupos, ver/crear jornadas (con grupo opcional), ver calendario y crear partidos desde allí, todo con selects de BD.

#### Scenario: Flujo completo desde TorneoDetalle
- **WHEN** admin entra a /torneos/1
- **THEN** ve tabs Equipos/Grupos/Jornadas/Calendario con acciones Crear/Añadir en cada una
