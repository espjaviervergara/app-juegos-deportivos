## Context

Torneo actual: `torneos.formato` enum sin tablas de grupos, `torneo_equipo` plano, `partidos` sin `grupo_id`. Jornadas son globales y no se puede mezclar grupos. Al ver torneo, admin solo ve Equipos/Jornadas/Calendario pero no puede crear grupos ni flexibilizar eliminatoria. Se requiere manual + automático con A/B/C y reagrupar, jornada multi-grupo y eliminatoria a elección del admin, en hosting básico con PHP puro.

## Goals / Non-Goals

**Goals:**
- Grupos por torneo con creación manual (nombre libre) y automática (N grupos A/B/C con reparto Round-Robin) y reagrupación posterior.
- Jornada con partidos de varios grupos (partido.grupo_id nullable, validado si presente).
- Eliminatoria flexible decidida por admin (no automática).
- UI en TorneoDetalle con tab Grupos y selects filtrados, calendario agrupado por jornada→grupo.

**Non-Goals:**
- Sorteo con bombos/semillas ni ranking previo.
- Generación automática de fixture por grupo (sigue manual).
- Clasificación por grupo separada (se puede añadir después, por ahora tabla global).

## Decisions

**D1 — Tablas `grupos` + `grupo_equipo`**
*Decisión:* `grupos(id, torneo_id, nombre, orden)` UNIQUE(torneo_id, nombre) + `grupo_equipo(grupo_id, equipo_id)` UNIQUE, FK a torneos/equipos. Equipos deben estar en `torneo_equipo` antes de asignar a grupo.
*Alternativas:* `grupo` como tag en `torneo_equipo` (descartado: pierde orden y queries).
*Razón:* Normalizado, permite reagrupar moviendo fila entre grupos sin tocar torneo_equipo.

**D2 — Creación automática A/B/C**
*Decisión:* `POST /torneos/:id/grupos/auto {numGrupos}` genera nombres `A,B,C...` hasta `Z` luego `AA`, y reparte `equipos del torneo` ordenados por id en Round-Robin (`i % numGrupos`). Transacción: borra grupos previos solo si admin confirma `replace:true`.
*Alternativa:* reparto por ranking (no hay datos).
*Razón:* Simple, determinístico, admin puede reagrupar después.

**D3 — Reagrupar**
*Decisión:* `PUT /grupos/reagrupar {movimientos:[{equipoId, fromGrupoId, toGrupoId}]}` o `POST /grupos/:id/equipos` + `DELETE` para mover uno a uno. Validado en transacción.
*Razón:* Evita endpoint complejo; drag en UI llama a add+delete.

**D4 — Jornada multi-grupo**
*Decisión:* `jornadas` no lleva `grupo_id`; `partidos.grupo_id` nullable. Crear partido acepta `grupoId?`; si se envía, validar que ambos equipos estén en ese grupo. Calendario query `LEFT JOIN grupos g ON g.id=p.grupo_id` y agrupar en PHP por `jornada_nro` luego `grupo_nombre`.
*Alternativa:* `jornada.grupo_id` (descartado: impide jornada con varios grupos).
*Razón:* Cumple requisito “una jornada puede tener partidos de varios grupos”.

**D5 — Formato y eliminatoria flexible**
*Decisión:* `torneos.formato` editable vía `PUT /torneos/:id`; no hay transición automática a eliminatoria. Admin crea grupos y luego, cuando decide, crea jornadas/partidos de eliminatoria (mismo flujo, sin grupo). Clasificación sigue global.
*Razón:* Flexibilidad pedida: admin elige.

**D6 — UI**
*Decisión:* `TorneoDetalle` tab Grupos con: `Crear grupo` (input nombre), `Auto: input numGrupos + botón Generar`, lista de grupos con chips de equipos + `Añadir equipo` (multiselect de `GET /torneos/:id/equipos` no asignados) + `Mover`. Partido form con `select Grupo (opcional)` que filtra `select EquipoA/B` a equipos de ese grupo.
*Razón:* Reusa selects de BD, solo nombres propios (grupo nombre) son manuales.

## Risks / Trade-offs

- **Grupo con nombre duplicado** → Mitigación: UNIQUE(torneo_id, nombre) + 409.
- **Equipo en dos grupos del mismo torneo** → Mitigación: UNIQUE(equipo_id) a nivel torneo no permitido; validar y 409 si ya está en otro grupo, sugiere mover.
- **Auto reparte desigual si equipos no divisibles** → Trade-off: grupos con ±1 equipo, aceptable y visible; admin reagrupa.
- **Jornada con partidos sin grupo y con grupo mezclados** → Mitigación: calendario muestra “Sin grupo” para null.
- **Borrar grupo con equipos/partidos** → Mitigación: `ON DELETE RESTRICT` si tiene `grupo_equipo` o `partidos.grupo_id`; admin debe vaciar primero.

## Migration Plan

1. Migración `015_grupos.sql`: `CREATE TABLE grupos...`, `CREATE TABLE grupo_equipo...`, `ALTER TABLE partidos ADD COLUMN grupo_id INT UNSIGNED NULL, ADD CONSTRAINT fk_partidos_grupo FOREIGN KEY...`, índices.
2. Seed no necesario.
3. API: nuevos `GrupoController` + `GrupoService`, modificar `PartidoService` validación y `CalendarioService`.
4. Frontend: `TorneoDetalle` tab Grupos, `Partido` form con grupo select, calendario agrupado.
5. Rollback: `DROP TABLE grupo_equipo, grupos`, `ALTER TABLE partidos DROP COLUMN grupo_id`.

## Open Questions

- ¿Clasificación por grupo separada o global alcanza por ahora?
- ¿Auto reparto debe mezclar aleatoriamente o mantener orden de inscripción?
