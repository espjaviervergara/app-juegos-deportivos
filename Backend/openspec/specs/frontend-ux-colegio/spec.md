## Requirements

### Requirement: Hero y secciones informativas para estudiantes
The system SHALL mostrar en Home un hero con nombre completo “Institución Educativa Ángela María Torres Suárez de Becerril Cesar”, lema deportivo, y botones a Programación y Tablas, seguido de secciones Valores (respeto, trabajo en equipo, juego limpio), Recomendaciones (hidratación, calentamiento, descanso) y footer con mensaje colaborativo.

#### Scenario: Estudiante entra sin login ve hero y secciones
- **WHEN** GET / sin auth
- **THEN** ve hero, puede hacer scroll a Programación/Tablas y leer Valores/Recomendaciones sin login

#### Scenario: Footer muestra mensaje colaborativo
- **WHEN** usuario hace scroll al final
- **THEN** ve “Hecho en colaboración del área de Tecnología y Deporte - Institución Educativa Ángela María Torres Suárez de Becerril Cesar” con año

### Requirement: Navegación simple y botón acceso admin/ayudante
The system SHALL tener header fijo con nombre corto del colegio y botón destacado “Acceso Administrador / Ayudante” que lleva a /login, visible en toda la SPA.

#### Scenario: Botón acceso lleva a login
- **WHEN** click en “Acceso Administrador / Ayudante”
- **THEN** navega a /login y tras login redirige a dashboard según rol

