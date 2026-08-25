## Why

Crear jornadas y partidos uno a uno es lento y propenso a error. Se necesita un asistente que genere el fixture completo preguntando ida/ida-y-vuelta, por grupo/sin asignar, y si va a eliminación directa, todo reasignable después desde jornada y calendario, con acceso admin rápido desde el listado de torneos.

## What Changes

- Botón **Generar fixture** en `TorneoDetalle` → Jornadas que abre wizard con pasos:
  1. **Tipo:** `ida` (1 partido por pareja) o `ida y vuelta` (2, local/visita invertido)
  2. **Ámbito:** `por grupo` (genera Round-Robin dentro de cada grupo) o `sin asignar` (genera entre todos los equipos del torneo sin asignar a jornada, queda en bolsa “Sin asignar”)
  3. **Destino:** si `sin asignar` deja `jornada_id=null` y `fechaHora` null; si elige jornada existente, asigna ahí y reparte fechas
- Segunda fase opcional: tras generar liga, pregunta **¿Va a eliminación directa?** Si `sí`, genera **eliminatoria automática** (semis/final según cantidad de clasificados, configurable) con partidos con `fase='eliminatoria'` y `jornada_id` de eliminatoria o sin asignar, reasignables.
- **Reasignar** desde Jornada (select jornada + input fechaHora) y desde Calendario (mismo) vía `PUT /partidos/:id {jornadaId, fechaHora, grupoId}` ya existente, ahora con soporte `jornada_id=null` para “Sin asignar”.
- **Botón Gestionar** en listado de torneos (`/` y `/torneos`) visible solo para `admin` → `Ver / Gestionar` → `/torneos/:id` con tabs.
- Validaciones: equipo en torneo, solapamiento <120m, grupo si se eligió, y `ida y vuelta` duplica con orden invertido.

## Capabilities

### New Capabilities
- `fixture-generacion`: generación Round-Robin ida/ida-y-vuelta, por grupo/sin asignar, y eliminatoria automática, con preguntas secuenciales.

### Modified Capabilities
- `jornadas-partidos`: partidos pueden quedar sin jornada (borrador, jornada_id nullable) y reasignarse; validación grupo y solapamiento ya existente se mantiene; wizard expone las opciones.
- `frontend-gestion`: wizard en TorneoDetalle Jornadas, botón Generar fixture con pasos, y botón Gestionar en listados.
- `frontend-calendario`: reasignación de jornada/fecha desde cards y lista “Sin asignar”.

## Impact

- DB: `partidos.jornada_id` pasa a nullable (ALTER), nueva columna `fase ENUM('liga','eliminatoria')` default liga, para distinguir eliminatoria auto.
- API: nuevo `POST /torneos/:id/fixture/generar` con body `{tipo: ida|ida_vuelta, ambito: grupo|sin_asignar, jornadaId?, numGrupos?}` y `POST /torneos/:id/fixture/eliminatoria {numClasificados, jornadaId?}`; modificación `GET /torneos/:id/partidos/sin-asignar` y `GET /torneos/:id/calendario` incluye sin asignar.
- Frontend: `TorneoDetalle` Jornadas con wizard (3 pasos + pregunta eliminatoria), `Calendario` con sección Sin asignar y reasignar, `Home`/`Torneos` con botón Gestionar solo admin.
