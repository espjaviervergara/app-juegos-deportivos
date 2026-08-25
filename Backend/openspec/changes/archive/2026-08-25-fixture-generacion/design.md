## Context

Actualmente jornada y partido son 1:N y partido requiere jornada_id no nulo, sin generación masiva. Admin crea uno a uno y reasigna con PUT. Se pide wizard que pregunte ida/ida-y-vuelta, por grupo/sin asignar, y si va a eliminatoria y la genere automático, todo reasignable desde jornada/calendario, con botón Gestionar en listados, en hosting básico PHP puro y React.

## Goals / Non-Goals

**Goals:**
- Wizard de 3 pasos + pregunta eliminatoria, con generación Round-Robin y opción sin asignar (jornada_id null, fase liga).
- Reasignar jornada/fecha/grupo desde jornada y calendario, validando solapamiento y pertenencia.
- Botón Gestionar solo admin en Home/Torneos.
- Partidos sin jornada visibles en “Sin asignar”.

**Non-Goals:**
- Calendario con drag&drop externo ni optimización de sede/horario.
- Generación de horarios con restricciones de cancha.
- Bracket visual interactivo de eliminatoria (solo lista de partidos).

## Decisions

**D1 — `partidos.jornada_id` nullable + `fase`**
*Decisión:* `ALTER TABLE partidos MODIFY jornada_id INT UNSIGNED NULL` y `ADD COLUMN fase ENUM('liga','eliminatoria') DEFAULT 'liga'`. Sin jornada = borrador “Sin asignar”.
*Alternativas:* tabla `partidos_borrador` (descartado: duplica).
*Razón:* Permite generar sin asignar y reasignar con mismo endpoint PUT.

**D2 — Generación ida / ida y vuelta por grupo o sin asignar**
*Decisión:* `POST /torneos/:id/fixture/generar {tipo: 'ida'|'ida_vuelta', ambito: 'grupo'|'sin_asignar', jornadaId?, grupoId?}`. Si `ambito=grupo`, itera por cada grupo y genera combinaciones `C(n,2)` dentro del grupo; si `sin_asignar`, ignora grupos y genera entre todos los equipos del torneo. `ida_vuelta` duplica con equipoA/B invertido y `fechaHora` +7 días si no hay jornada. Si `jornadaId` provisto, asigna ahí; si no, deja null.
*Razón:* Cubre 4 combinaciones pedidas con un solo endpoint.

**D3 — Eliminatoria automática**
*Decisión:* `POST /torneos/:id/fixture/eliminatoria {numClasificados: 2|4|8, jornadaId?}`. Si torneo tiene grupos, clasifica top 1 de cada grupo (orden por tabla actual o por id si no hay partidos); si no tiene grupos, top N de tabla global. Genera `numClasificados/2` partidos con `fase='eliminatoria'`, sin asignar o en jornada dada, con `grupo_id=null`. No genera bracket recursivo, solo primera ronda; admin crea siguientes manualmente.
*Razón:* Suficiente para “va por eliminación directa y que se genere en automático” sin complejidad de árbol.

**D4 — Reasignar desde jornada y calendario**
*Decisión:* Reusa `PUT /partidos/:id {jornadaId, fechaHora, grupoId}` con validación ya existente (equipo en torneo/grupo, solapamiento). Calendario y Jornadas muestran select Jornada (incluye “Sin asignar”) + input datetime. Lista “Sin asignar” es `GET /torneos/:id/partidos/sin-asignar` (partidos con jornada_id IS NULL).
*Razón:* Un solo endpoint para todas las reasignaciones.

**D5 — Botón Gestionar**
*Decisión:* En `Home` y `Torneos` cards, si `isAdmin`, muestra `Gestionar` → `/torneos/:id` (tabs). No para editor/público.
*Razón:* Acceso rápido pedido.

**D6 — UI wizard**
*Decisión:* Modal en `TorneoDetalle` Jornadas con pasos: Paso1 radio `ida/ida_vuelta`, Paso2 radio `por grupo/sin asignar` (si torneo tiene grupos, default por grupo), Paso3 select `Jornada` (opcional, + “Sin asignar”), botón Generar. Tras éxito, pregunta modal “¿Va a eliminación directa?” → si sí, pide `numClasificados` y genera.
*Razón:* Secuencial y claro, sin sobrecargar.

## Risks / Trade-offs

- **Generar duplicados si ya hay partidos** → Mitigación: antes de generar, `SELECT COUNT(*) WHERE torneo tiene partidos con mismos pares y fase liga` y advertir, pero no bloquear; admin puede borrar.
- **Partido sin jornada invisible en calendario actual** → Mitigación: nueva sección “Sin asignar” en calendario y en jornadas.
- **Jornada con partidos de varios grupos ya permitido, pero ahora con null** → Mitigación: calendario agrupa “Sin grupo” para null.
- **Eliminatoria con numClasificados no potencia de 2** → Mitigación: validar 2/4/8, 422 si otro.

## Migration Plan

1. Migración `018_fixture.sql`: `MODIFY jornada_id NULL`, `ADD fase`, índice.
2. Nuevo `FixtureController` + rutas.
3. Frontend: wizard en `TorneoDetalle`, calendario sin asignar, botón Gestionar.
4. Rollback: `MODIFY jornada_id NOT NULL` (requiere borrar partidos sin jornada) + `DROP COLUMN fase`.

## Open Questions

- ¿Eliminatoria genera solo semifinal/final o también cuartos?
- ¿Reparto de fechas para ida y vuelta debe ser +7 días automático o manual?
