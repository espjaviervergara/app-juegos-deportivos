## Context

Backend archivado `2026-08-24-torneos-api-v1` expone `/api/v1/` con JWT (access Bearer + refresh httpOnly), RBAC por `usuario_torneo`, y lectura pública para calendario/clasificaciones. `Frontend/` está vacío. Hosting es básico (Apache + PHP, sin Node en prod, solo estático). Se requiere dashboard único con permisos, calendario en cards por jornada y tabla clásica, con Tailwind + Bootstrap, consumiendo la misma API para web y futuro móvil.

## Goals / Non-Goals

**Goals:**
- SPA React + Vite con Tailwind (utilidades) + Bootstrap (componentes) que consuma `/api/v1/` con refresh silencioso y guards por rol.
- Dashboard único que oculta/muestra navegación según `admin` vs `editor` (y torneos asignados), con UX clara para 403/429/409.
- Calendario como cards por jornada y clasificación como tabla `PJ/PG/PE/PP/GF/GC/GA/Pts`, ambos paginados y públicos.
- Build estático desplegable en el mismo hosting básico que el backend (copiar `dist/` a `public/` o subcarpeta).

**Non-Goals:**
- SSR/Next.js, PWA/offline, ni app móvil nativa en esta fase.
- Rediseño de API (solo consumo); no se tocan `openspec/specs/` del backend.
- Tests E2E completos (solo smoke).

## Decisions

**D1 — Vite + React + React Router (SPA)**
*Razón:* Rápido, ligero, compatible con hosting estático; alternativa Next.js descartada por requerir Node en prod. `vite.config.js` con `proxy: { '/api': 'http://localhost:8000' }` en dev.

**D2 — Tailwind + Bootstrap juntos**
*Razón:* Tailwind para layout/utilidades, Bootstrap para componentes probados (modal, badge, table, pagination). Alternativa solo Tailwind descartada por tiempo de UI. Config: `tailwind.config.js` + `import 'bootstrap/dist/css/bootstrap.min.css'` + purge.
*Trade-off:* CSS más pesado; mitigado con purge y import selectivo.

**D3 — Servicio API central (`src/services/api.js`) con interceptor**
*Razón:* `fetch` wrapper que añade `Authorization`, detecta 401, hace `POST /api/v1/auth/refresh` (cookie), reintenta una vez con cola para evitar race. Alternativa axios descartada por dependencia extra. `.env` con `VITE_API_BASE=/api/v1`.

**D4 — Dashboard único con permisos**
*Razón:* Una sola app con `AuthContext` (`user {id,rol}`) y `usePermisos(torneoId)` que chequea `GET /mis-torneos` o `usuario_torneo`. Sidebar filtra items; rutas protegidas con `<RequireRole rol="admin">` y `<RequireTorneo>`. Alternativa dos apps (public/admin) descartada por duplicación.

**D5 — Calendario cards por jornada**
*Razón:* Card por jornada (`nro + fecha`) que lista partidos (`equipoA vs equipoB + fechaHora + badge estado`). Paginación `page/limit` contra `GET /torneos/:id/calendario`. Alternativa tabla descartada por UX móvil.

**D6 — Clasificación tabla clásica**
*Razón:* `<table>` Bootstrap con columnas `PJ/PG/PE/PP/GF/GC/GA/Pts`, sort `puntos→GA→GF` ya viene ordenada del backend; solo render. Stats por equipo/jugador en modal/drawer.

**D7 — Hosting básico**
*Razón:* Build `npm run build` → `Frontend/dist/` estático; copiar a `Backend/public/app` o a `htdocs/app` y `.htaccess` con `FallbackResource /app/index.html` para SPA. API sigue en `/api/v1/` mismo dominio, evita CORS extra. Alternativa Vercel descartada por requisito mismo hosting.

## Risks / Trade-offs

- **Bootstrap + Tailwind colisión de estilos** → Mitigación: Tailwind `preflight` desactivado parcialmente o orden de imports, prefijo `tw-` si choca.
- **Refresh silencioso race (múltiples 401)** → Mitigación: cola de peticiones en `api.js` (una sola refresh a la vez, resto esperan).
- **Dashboard único filtra mal torneos de editor** → Mitigación: guardar `misTorneos` en context y validar cada fetch; 403 muestra "No asignado".
- **Hosting básico sin fallback SPA** → Mitigación: `.htaccess` con `RewriteRule ^app/.*$ app/index.html` o copiar `index.html` como `404.html`.
- **Tabla clasificación con 100 equipos paginada** → Mitigación: paginación backend ya limita; frontend solo slice.

## Migration Plan

1. `npm create vite@latest Frontend -- --template react`, instalar `react-router-dom`, `tailwindcss`, `bootstrap`.
2. Configurar `tailwind.config.js`, `postcss`, `.env.example` (`VITE_API_BASE=/api/v1`), `vite.config.js` proxy.
3. Scaffold `src/services/api.js`, `AuthContext`, `router.jsx`, `Layout`, páginas públicas y protegidas.
4. `npm run build` → copiar `Frontend/dist/*` a `Backend/public/app` (o `C:/laragon/www/app`) y probar `GET /api/v1/deportes` + login.
5. Rollback: borrar `Frontend/dist` y revertir `git`.

## Open Questions

- ¿Prefijo de ruta del frontend en hosting: `/` (raíz) o `/app`?
- ¿Paginación infinita o botones `Anterior/Siguiente` para calendario/tabla?
