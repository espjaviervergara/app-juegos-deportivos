## Context

SPA usada por estudiantes de colegio (12-18 años) para ver programación y tablas sin login, y por admin/ayudante para gestionar. Colegio Ángela María Torres Suárez de Becerril Cesar quiere promover deporte, buena actitud y recomendaciones. Hosting básico, Tailwind+Bootstrap, React. Se necesita UX atractiva, no técnica.

## Goals / Non-Goals

**Goals:**
- Hero con nombre colegio, lema y CTA a Programación y Tablas, con colores vivos y responsive.
- Secciones: Valores del deporte, Recomendaciones (hidratación, calentamiento, respeto), Programación y Tabla con UI amigable.
- Footer con mensaje colaborativo Tecnología y Deporte.
- Botón Acceso Administrador/Ayudante visible pero no intrusivo.

**Non-Goals:**
- Sistema de noticias/blog ni galería de fotos dinámica.
- Cambio en API o permisos.

## Decisions

**D1 — Hero + secciones ancla**
*Razón:* Estudiantes entran y ven de inmediato qué es, sin login. Secciones con scroll suave, iconos y colores por deporte. Alternativa landing separada descartada por SPA única.

**D2 — Tabla y cards con gamificación suave**
*Razón:* Medallas 🥇🥈🥉 para top 3, badges de estado con colores, y cards con iconos ⚽🏀🏐. Hace atractivo sin sobrecargar.

**D3 — Textos promoción y Recomendaciones**
*Razón:* Bloque “Juega limpio, respeta, diviértete” y tips de hidratación/calentamiento, con lenguaje cercano a estudiantes.

**D4 — Footer colaborativo**
*Razón:* Mensaje fijo “Hecho en colaboración del área de Tecnología y Deporte - IE Ángela María Torres Suárez de Becerril Cesar” con año.

**D5 — Mantener dashboard único**
*Razón:* No se duplica auth; solo se destaca botón “Acceso Administrador / Ayudante” en header.

## Risks / Trade-offs

- **Demasiado texto abruma** → Mitigación: secciones colapsables y cards cortas.
- **Imágenes pesadas en hosting básico** → Mitigación: usar gradientes + emojis/svg inline, no fotos grandes.

## Migration Plan

1. Actualizar `Home.jsx`, `Layout.jsx`, `Calendario.jsx`, `Clasificacion.jsx`, `index.css`.
2. Build y verificar en `http://localhost:5173`.

## Open Questions

- ¿Colores institucionales del colegio (para usar en hero)?
