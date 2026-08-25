## ADDED Requirements

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
