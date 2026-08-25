## ADDED Requirements

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
