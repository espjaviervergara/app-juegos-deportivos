## Requirements

### Requirement: CRUD admin para deportes/torneos/equipos/jugadores/jornadas/partidos
The system SHALL proveer formularios CRUD para admin (deportes, torneos con validación deporte activo y M/F/Mixto, equipos, jugadores con dorsal único, jornadas, partidos con fechaHora y equipos del torneo), mostrando 409 para solapamiento <120m.

#### Scenario: Crear partido con solapamiento muestra 409
- **WHEN** admin crea partido del mismo equipo dentro de 120m
- **THEN** muestra error "Equipo con partido solapado" sin cerrar modal

#### Scenario: Attach/detach equipo a torneo
- **WHEN** admin hace attach de equipo a torneo
- **THEN** lista se actualiza y muestra toast éxito

### Requirement: Validación y paginación en gestión
The system SHALL validar campos requeridos (422 con details por campo) y paginar listados con page/limit.

#### Scenario: Validación muestra details
- **WHEN** POST sin nombre
- **THEN** marca campo con mensaje de details


## Requirements


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


## Requirements


### Requirement: Wizard de generación con preguntas secuenciales
The system SHALL mostrar en TorneoDetalle Jornadas un botón Generar fixture que abre wizard con pasos: 1) tipo ida/ida_vuelta (radio), 2) ámbito por grupo/sin asignar (radio, solo si hay grupos), 3) select Jornada destino (opcional, incluye Sin asignar), y botón Generar que llama POST /fixture/generar.

#### Scenario: Wizard genera ida por grupo sin asignar
- **WHEN** usuario elige ida + por grupo + Sin asignar y hace Generar
- **THEN** partidos quedan en lista Sin asignar

### Requirement: Pregunta eliminatoria tras liga
The system SHALL tras generar liga mostrar modal ¿Va a eliminación directa? con input numClasificados y generar si confirma vía POST /fixture/eliminatoria.

#### Scenario: Confirma eliminatoria 4
- **WHEN** usuario confirma con 4
- **THEN** genera 2 partidos eliminatoria

### Requirement: Botón Gestionar en listados solo admin
The system SHALL mostrar en Home y Torneos cards botón Gestionar/Ver que lleva a /torneos/:id solo para admin.

#### Scenario: Admin ve Gestionar, público no
- **WHEN** admin logueado ve Home
- **THEN** ve botón Gestionar; anónimo no

