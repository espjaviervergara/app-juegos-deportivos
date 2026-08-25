## Requirements

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


## Requirements


### Requirement: Calendario muestra grupo del partido
The system SHALL en cards por jornada mostrar badge de grupo (si partido tiene grupo_nombre) y agrupar visualmente por jornada y luego por grupo.

#### Scenario: Card jornada con dos grupos
- **WHEN** jornada 1 tiene partido Grupo A y partido Grupo B
- **THEN** card muestra sección "Grupo A" y "Grupo B" con sus partidos

### Requirement: Jornada puede tener partidos de varios grupos
The system SHALL permitir que una jornada liste partidos de distintos grupos sin error.

#### Scenario: Jornada mixta se renderiza sin error
- **WHEN** GET /torneos/1/calendario devuelve jornada con 3 grupos distintos
- **THEN** UI los agrupa correctamente sin colapsar


## Requirements


### Requirement: Calendario muestra Sin asignar y permite reasignar
The system SHALL en Calendario mostrar sección Sin asignar con partidos jornada_id null y en cada card select Jornada + input fechaHora para reasignar vía PUT /partidos/:id.

#### Scenario: Reasignar desde calendario
- **WHEN** usuario cambia Jornada de partido sin asignar a Jornada 2
- **THEN** PUT y card se mueve a Jornada 2

### Requirement: Agrupación por jornada y grupo se mantiene
The system SHALL seguir agrupando calendario por jornada_nro y luego por grupo_nombre, incluyendo Sin asignar.

#### Scenario: Jornada con varios grupos y sin asignar
- **WHEN** calendario tiene jornada 1 con Grupo A, Grupo B y Sin asignar
- **THEN** muestra 3 secciones correctamente


## Requirements


### Requirement: Cards de calendario más visuales y atractivas
The system SHALL mostrar cards por jornada con iconos por deporte, badges de color por estado (pendiente/verde finalizado), y fecha legible, con layout responsive y animación suave.

#### Scenario: Card muestra icono y badge de color
- **WHEN** GET /torneos/1/calendario
- **THEN** cada partido muestra ⚽/🏀/🏐 según deporte y badge verde para finalizado


