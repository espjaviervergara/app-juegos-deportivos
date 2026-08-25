## Why

La SPA actual es funcional pero técnica (tablas y formularios). Para la Institución Educativa Ángela María Torres Suárez de Becerril Cesar se necesita una experiencia atractiva para estudiantes de colegio que motive el deporte, muestre programación y tablas de forma clara y promueva buena actitud deportiva, manteniendo el acceso admin/ayudante para gestión.

## What Changes

- Rediseño SPA pública (sin login) con hero con nombre del colegio, lema deportivo, imágenes y colores atractivos (Tailwind + Bootstrap), secciones: Bienvenida/Valores, Programación (cards por jornada con grupo y fecha), Tabla de Posiciones (clásica), Recomendaciones y Fair Play, y footer con mensaje “Hecho en colaboración del área de Tecnología y Deporte - Institución Educativa Ángela María Torres Suárez de Becerril Cesar”.
- Navegación simple para estudiantes: menú fijo con anclas a Programación/Tablas/Valores/Recomendaciones y botón destacado “Acceso Administrador/ Ayudante” que lleva a /login → dashboard según rol (admin gestiona todo, ayudante solo coloca resultados).
- Mejora UX de tablas y cards: badges de estado, iconos deportivos, responsive mobile-first, skeletons y empty states amigables.
- Mantener dashboard único con permisos, pero con layout más visual y mensajes motivacionales.

## Capabilities

### New Capabilities
- `frontend-ux-colegio`: hero, secciones informativas, navegación estudiantil, footer colaborativo, estilos atractivos.

### Modified Capabilities
- `frontend-calendario`: cards más visuales con iconos y colores por estado.
- `frontend-clasificaciones`: tabla con medallas y resaltado líder.
- `frontend-layout`: header con nombre colegio y botón acceso admin/ayudante destacado.

## Impact

- Solo Frontend (`Frontend/src/pages/Home.jsx`, `Layout.jsx`, `Calendario.jsx`, `Clasificacion.jsx`, `index.css`); sin cambios en API.
- Requiere imágenes ilustrativas (usar placeholders/emojis si no hay assets) y textos de promoción deportiva.
- Build estático sigue igual para Hostinger.
