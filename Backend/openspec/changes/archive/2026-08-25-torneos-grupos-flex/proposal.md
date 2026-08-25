## Why

El formato actual `liga/eliminatoria/grupos+eliminatoria` es solo un enum sin estructura. Al entrar a un torneo el admin no puede crear grupos, asignar equipos a grupos ni flexibilizar eliminatorias, y una jornada no puede mezclar grupos. Se necesita modelo de grupos con creación manual y automática (A/B/C) y reagrupación para que el admin tenga control total.

## What Changes

- Nuevo modelo `grupos(id, torneo_id, nombre UNIQUE por torneo, orden)` + `grupo_equipo(grupo_id, equipo_id)` con validación de equipo ya inscrito en torneo.
- Creación **manual**: admin crea grupo con nombre libre y añade equipos con multiselect (solo equipos del torneo).
- Creación **automática**: admin indica número de grupos (ej. 4) → sistema genera `Grupo A/B/C/D` y reparte equipos existentes de forma Round-Robin; admin puede **reagrupar** moviendo equipos entre grupos (drag o select).
- **BREAKING**: `partidos` añade `grupo_id` nullable (FK a grupos) para asociar partido a grupo; una **jornada puede tener partidos de varios grupos** (no se fuerza 1:1). Validación: si `grupo_id` presente, ambos equipos deben pertenecer a ese grupo.
- Formato y fase flexibilizados: admin elige `formato` al crear torneo pero puede cambiarlo después; para eliminatoria, admin decide cuándo crear fase eliminatoria (no automática por grupos).
- UI: `TorneoDetalle` nuevo tab **Grupos** (lista, crear manual/automático, reagrupar), `Jornada` form con `select Grupo (opcional)`, `Partido` form filtra equipos por torneo y por grupo si se eligió, `Calendario` agrupa cards por jornada y luego por grupo.

## Capabilities

### New Capabilities
- `grupos`: CRUD de grupos por torneo (manual y automático), asignación/reagrupación de equipos a grupos, validación equipo en torneo.

### Modified Capabilities
- `jornadas-partidos`: jornadas pueden contener partidos de varios grupos; partidos con `grupo_id` opcional y validación de pertenencia; calendario agrupa por jornada→grupo.
- `torneos`: formato editable y soporte para fase de grupos + eliminatoria flexible (admin decide).
- `frontend-gestion`: UI para crear grupos manual/automático, reagrupar, y selects filtrados por grupo.
- `frontend-calendario`: cards por jornada que muestran grupo del partido.

## Impact

- DB: nuevas tablas `grupos`, `grupo_equipo`, columna `partidos.grupo_id` nullable + índices; migración y seed.
- API: nuevos endpoints `POST /torneos/:id/grupos`, `POST /torneos/:id/grupos/auto`, `POST /grupos/:id/equipos`, `DELETE /grupos/:id/equipos/:equipoId`, `PUT /grupos/reagrupar`; modificación `POST /jornadas/:id/partidos` y `GET /torneos/:id/calendario` para incluir grupo.
- Frontend: `TorneoDetalle` tab Grupos, `Partido` form con selects filtrados, `Calendario` agrupado.
- No cambia auth ni clasificaciones (clasificación por grupo se puede añadir después si se necesita).
