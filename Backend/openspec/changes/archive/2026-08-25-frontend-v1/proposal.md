## Why

El backend `torneos-api-v1` ya expone `/api/v1/` con auth JWT, torneos, calendario y clasificaciones, pero no hay interfaz para consumirlo. Se necesita un frontend único que sirva tanto al público (calendario y tabla) como a los dos roles (admin/editor) en el mismo hosting básico, con Tailwind + Bootstrap y UX clara para jornadas en cards y clasificación clásica.

## What Changes

- Frontend Vite + React (SPA) con Tailwind + Bootstrap (utilidades + componentes), servido desde `Frontend/` y buildeado a estático para hosting básico (mismo dominio que `Backend/public`).
- Consumo de API vía `fetch` con `.env` (`VITE_API_BASE=/api/v1`), interceptor que añade `Authorization: Bearer <access>` y hace refresh silencioso con `POST /auth/refresh` (cookie httpOnly) al 401, con cola de reintento.
- Dashboard único con permisos: sidebar y rutas filtradas por `rol` (`admin` vs `editor`) y por `usuario_torneo` (editor ve solo asignados); guards en router y 403 amigable.
- Vistas públicas: listado torneos paginado con filtros `deporte/categoria`, detalle torneo con tabs Equipos/Jornadas/Calendario/Clasificación sin login.
- Calendario como **cards por jornada** (no tabla), paginado, ordenado por `fechaHora`, con badges `PENDIENTE/RECHAZADO/OFICIAL`.
- Clasificación como **tabla clásica** `PJ/PG/PE/PP/GF/GC/GA/Pts` ordenada `puntos→GA→GF`, con paginación y stats por equipo/jugador.
- CRUD admin (deportes, torneos, equipos/jugadores, jornadas/partidos con validación solapamiento 409) y flujo resultados (editor propone goles/tarjetas, admin aprueba/rechaza con motivo, reenvío versionado) + auditoría solo admin.
- Build estático (`dist/`) copiable a `Backend/public` o subcarpeta del hosting básico, con fallback `index.html` para SPA y `X-HTTP-Method-Override` ya soportado por API.

## Capabilities

### New Capabilities
- `frontend-auth`: login, refresh silencioso, logout, guards por rol/asignación, manejo 401/403/429, persistencia de usuario.
- `frontend-layout`: layout dashboard único, sidebar/nav por permisos, responsive, manejo de errores globales y toasts.
- `frontend-torneos`: listado público torneos paginado + detalle con tabs y filtros.
- `frontend-calendario`: cards por jornada, paginación, badges de estado, vista calendario público.
- `frontend-clasificaciones`: tabla clásica PJ/PG/PE/PP/GF/GC/GA/Pts + estadísticas equipo/jugador aisladas por torneo.
- `frontend-gestion`: CRUD admin para deportes/torneos/equipos/jugadores/jornadas/partidos (con 409 solapamiento) y attach/detach.
- `frontend-resultados`: propuesta de resultados (editor), aprobación/rechazo con motivo (admin), versionado y reenvío, auditoría.
- `frontend-build`: configuración Vite, Tailwind+Bootstrap, env, build estático para hosting básico y deploy en mismo dominio.

### Modified Capabilities
- Ninguna (frontend greenfield; `openspec/specs/` actuales son de API y no cambian).

## Impact

- Nuevo código en `Frontend/` (`src/pages|components|hooks|services/api.js|router|store`), `vite.config.js`, `tailwind.config.js`, `.env.example`.
- Requiere `Backend` corriendo en `/api/v1/` (CORS allowlist ya configurado); sin cambios en API, solo consumo.
- Dependencias: `react`, `react-router`, `tailwindcss`, `bootstrap`; build genera `Frontend/dist/` para copiar a hosting básico.
- Afecta docs: actualizar `readmi.md` con instrucciones de build y deploy junto a `Backend/public`.
