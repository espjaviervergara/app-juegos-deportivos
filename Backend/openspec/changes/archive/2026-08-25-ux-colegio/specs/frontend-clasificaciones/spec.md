## ADDED Requirements

### Requirement: Tabla con medallas y resaltado
The system SHALL mostrar tabla con medallas 🥇🥈🥉 para top 3, fila líder resaltada y badges de puntos, con diseño atractivo para estudiantes.

#### Scenario: Top 3 con medallas
- **WHEN** GET /torneos/1/clasificaciones
- **THEN** filas 1-3 muestran 🥇🥈🥉 y líder con fondo destacado
