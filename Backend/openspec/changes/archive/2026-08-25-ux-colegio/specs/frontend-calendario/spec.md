## ADDED Requirements

### Requirement: Cards de calendario más visuales y atractivas
The system SHALL mostrar cards por jornada con iconos por deporte, badges de color por estado (pendiente/verde finalizado), y fecha legible, con layout responsive y animación suave.

#### Scenario: Card muestra icono y badge de color
- **WHEN** GET /torneos/1/calendario
- **THEN** cada partido muestra ⚽/🏀/🏐 según deporte y badge verde para finalizado

